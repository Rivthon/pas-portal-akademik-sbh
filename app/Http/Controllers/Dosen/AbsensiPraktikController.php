<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPraktik;
use App\Models\AsprakAssignment;
use App\Models\AsprakAttendance;
use App\Models\JadwalPraktik;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use App\Services\JadwalPraktikAssignmentService;
use App\Support\KrsClassResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbsensiPraktikController extends Controller
{
    public function index(Request $request, JadwalPraktikAssignmentService $jadwalPraktikService)
    {
        $dosen = auth('dosen')->user();
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $jadwal = collect();

        if ($activeTa) {
            $jadwalPraktikService->ensureForDosen($dosen, $activeTa);

            $jadwal = $this->jadwalDosenQuery()
                ->where('ta_id', $activeTa->ta_id)
                ->with([
                    'kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen', 'programStudi', 'ruangan',
                    'asprakAssignments' => fn ($query) => $query->where('aktif', true)->with('mahasiswa')->orderBy('id'),
                    'pertemuan' => fn ($query) => $query
                        ->where('dosen_id', $dosen->dosen_id)
                        ->with('asprakAttendances.assignment.mahasiswa')
                        ->withCount('absensi')
                        ->orderByDesc('tanggal_pertemuan'),
                ])
                ->when($request->filled('search'), function (Builder $query) use ($request) {
                    $search = trim($request->string('search')->toString());
                    $query->where(function (Builder $query) use ($search) {
                        $query->whereHas('kurikulum.mataKuliah', fn (Builder $query) => $query
                            ->where('nama', 'like', '%'.$search.'%')
                            ->orWhere('matakuliah_id', 'like', '%'.$search.'%'))
                            ->orWhereHas('kurikulum.dosenToMatakuliah.dosen', fn (Builder $query) => $query
                                ->where('nama', 'like', '%'.$search.'%'));
                    });
                })
                ->when(
                    in_array($request->input('jenis_kelas'), ['reguler', 'karyawan'], true),
                    fn (Builder $query) => $query->whereRaw('LOWER(jenis_kelas) = ?', [$request->input('jenis_kelas')])
                )
                ->orderBy('hari')->orderBy('jam_mulai')->get();
        }

        $jadwalPerSemester = $jadwal
            ->groupBy(fn (JadwalPraktik $item) => (int) ($item->kurikulum?->mataKuliah?->smt
                ?: $item->kurikulum?->mataKuliah?->semester))
            ->sortKeys();
        $statistik = [
            'total' => $jadwal->count(),
            'reguler' => $jadwal->filter(fn (JadwalPraktik $item) => KrsClassResolver::normalize($item->jenis_kelas) === 'reguler')->count(),
            'karyawan' => $jadwal->filter(fn (JadwalPraktik $item) => KrsClassResolver::normalize($item->jenis_kelas) === 'karyawan')->count(),
        ];

        return view('dosen.absensi-praktik.index', compact('jadwal', 'jadwalPerSemester', 'activeTa', 'statistik'));
    }

    public function storePertemuan(Request $request)
    {
        $validated = $request->validate([
            'jadwal_praktik_id' => ['required', 'integer', 'exists:jadwal_praktik,id'],
            'tanggal_pertemuan' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode_pbm' => ['required', 'in:online,offline'],
            'topik' => ['required', 'string', 'max:255'],
            'sub_topik' => ['nullable', 'string', 'max:255'],
            'asprak_ids' => ['nullable', 'array'],
            'asprak_ids.*' => ['integer', 'distinct'],
        ]);

        $jadwal = $this->findJadwalDosenOrFail((int) $validated['jadwal_praktik_id']);
        $asprakIds = $this->validatedAsprakAssignmentIds($jadwal, $validated['asprak_ids'] ?? [], true);
        $meetingData = collect($validated)->except('asprak_ids')->all();
        $pertemuan = DB::transaction(function () use ($meetingData, $jadwal, $asprakIds) {
            $pertemuan = PertemuanPraktik::create([...$meetingData, 'dosen_id' => auth('dosen')->id()]);
            $now = now();
            $rows = $this->pesertaDisetujuiIds($jadwal)->map(fn ($mahasiswaId) => [
                'jadwal_praktik_id' => $jadwal->id,
                'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
                'mahasiswa_id' => $mahasiswaId,
                'tanggal' => $meetingData['tanggal_pertemuan'],
                'status' => 'belum diabsen',
                'keterangan' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            if ($rows->isNotEmpty()) {
                AbsensiPraktik::insert($rows->all());
            }
            foreach ($asprakIds as $asprakAssignmentId) {
                AsprakAttendance::create([
                    'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
                    'asprak_penugasan_id' => $asprakAssignmentId,
                    'status' => 'belum diabsen',
                ]);
            }

            return $pertemuan;
        });

        activity_log('buat_pertemuan_praktik', 'Dosen membuat pertemuan praktik ID: '.$pertemuan->pertemuan_praktik_id);

        return redirect()->route('dosen.absensi-praktik.show', $pertemuan)
            ->with('success', 'Pertemuan praktik berhasil dibuat. Silakan isi kehadiran mahasiswa.');
    }

    public function show(PertemuanPraktik $pertemuan)
    {
        $pertemuan->load([
            'jadwal.kurikulum.mataKuliah',
            'jadwal.ruangan',
            'asprakAttendances.assignment.mahasiswa',
        ]);
        $this->ensurePertemuanMilikDosen($pertemuan);
        $this->syncPesertaDisetujui($pertemuan);

        $absensi = $pertemuan->absensi()->with('mahasiswa')->get()->keyBy('mahasiswa_id');
        $daftarPeserta = $this->daftarPesertaAbsensi($pertemuan->jadwal)
            ->map(function (array $peserta) use ($absensi) {
                $peserta['absensi'] = $absensi->get($peserta['mahasiswa']->mahasiswa_id);

                return $peserta;
            });

        return view('dosen.absensi-praktik.show', compact('pertemuan', 'daftarPeserta'));
    }

    public function manageAsprak(JadwalPraktik $jadwal)
    {
        $jadwal = $this->findJadwalDosenOrFail((int) $jadwal->id);
        $jadwal->load(['kurikulum.mataKuliah', 'programStudi', 'tahunAjaran', 'asprakAssignments.mahasiswa']);
        $jurusanId = $jadwal->jurusan_id ?: $jadwal->kurikulum?->jurusan_id;
        $candidates = Mahasiswa::query()
            ->where('jurusan_id', $jurusanId)
            ->whereRaw('LOWER(status_mhs) = ?', ['aktif'])
            ->orderBy('nama')
            ->get(['mahasiswa_id', 'nim', 'nama', 'semester', 'kelas']);
        $activeIds = $jadwal->asprakAssignments->where('aktif', true)
            ->pluck('mahasiswa_id')->map(fn ($id) => (int) $id)->all();

        return view('dosen.absensi-praktik.asprak', compact('jadwal', 'candidates', 'activeIds'));
    }

    public function updateAsprak(Request $request, JadwalPraktik $jadwal)
    {
        $jadwal = $this->findJadwalDosenOrFail((int) $jadwal->id);
        $this->ensureJadwalAktif($jadwal);
        $validated = $request->validate([
            'mahasiswa_ids' => ['nullable', 'array'],
            'mahasiswa_ids.*' => ['integer', 'distinct', 'exists:mahasiswa,mahasiswa_id'],
        ]);
        $selectedIds = collect($validated['mahasiswa_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $jurusanId = (string) ($jadwal->jurusan_id ?: $jadwal->kurikulum?->jurusan_id);
        $validIds = Mahasiswa::query()
            ->whereIn('mahasiswa_id', $selectedIds)
            ->where('jurusan_id', $jurusanId)
            ->whereRaw('LOWER(status_mhs) = ?', ['aktif'])
            ->pluck('mahasiswa_id')->map(fn ($id) => (int) $id);
        abort_unless($validIds->count() === $selectedIds->count(), 422, 'Asprak harus merupakan mahasiswa aktif dari program studi mata kuliah ini.');

        DB::transaction(function () use ($jadwal, $selectedIds) {
            $jadwal->asprakAssignments()->whereNotIn('mahasiswa_id', $selectedIds)->update(['aktif' => false]);
            foreach ($selectedIds as $mahasiswaId) {
                AsprakAssignment::updateOrCreate(
                    ['jadwal_praktik_id' => $jadwal->id, 'mahasiswa_id' => $mahasiswaId],
                    ['ditugaskan_oleh_dosen_id' => auth('dosen')->id(), 'aktif' => true]
                );
            }
        });

        activity_log('kelola_asprak', 'Dosen memperbarui Asprak jadwal praktik ID: '.$jadwal->id);

        return redirect()->route('dosen.absensi-praktik.index')->with('success', 'Daftar Asprak berhasil diperbarui.');
    }

    public function update(Request $request, PertemuanPraktik $pertemuan)
    {
        $pertemuan->load('jadwal');
        $this->ensurePertemuanMilikDosen($pertemuan);
        $validated = $request->validate([
            'status' => ['required', 'array', 'min:1'],
            'status.*' => ['required', 'in:hadir,izin,sakit,tidak hadir'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:500'],
        ]);

        $mahasiswaIds = collect(array_keys($validated['status']))->map(fn ($id) => (int) $id)->unique();
        $validIds = $this->pesertaDisetujuiIds($pertemuan->jadwal)->intersect($mahasiswaIds);
        abort_unless($validIds->count() === $mahasiswaIds->count(), 422, 'Terdapat mahasiswa yang belum memiliki KRS disetujui pada kelas praktik ini.');

        DB::transaction(function () use ($validated, $pertemuan, $mahasiswaIds) {
            foreach ($mahasiswaIds as $mahasiswaId) {
                AbsensiPraktik::updateOrCreate(
                    ['pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id, 'mahasiswa_id' => $mahasiswaId],
                    [
                        'jadwal_praktik_id' => $pertemuan->jadwal_praktik_id,
                        'tanggal' => $pertemuan->tanggal_pertemuan,
                        'status' => $validated['status'][$mahasiswaId],
                        'keterangan' => $validated['keterangan'][$mahasiswaId] ?? null,
                    ]
                );
            }
        });

        activity_log('simpan_absensi_praktik', 'Dosen menyimpan absensi praktik pertemuan ID: '.$pertemuan->pertemuan_praktik_id);

        return back()->with('success', 'Absensi praktik berhasil disimpan.');
    }

    public function updateAsprakAttendance(Request $request, PertemuanPraktik $pertemuan)
    {
        $pertemuan->load('jadwal');
        $this->ensurePertemuanMilikDosen($pertemuan);
        $validated = $request->validate([
            'status_asprak' => ['required', 'array', 'min:1'],
            'status_asprak.*' => ['required', 'in:hadir,izin,sakit,tidak hadir'],
            'keterangan_asprak' => ['nullable', 'array'],
            'keterangan_asprak.*' => ['nullable', 'string', 'max:500'],
        ]);
        $attendanceIds = collect(array_keys($validated['status_asprak']))->map(fn ($id) => (int) $id)->unique();
        $attendances = $pertemuan->asprakAttendances()->whereIn('id', $attendanceIds)->get()->keyBy('id');
        abort_unless($attendances->count() === $attendanceIds->count(), 403, 'Terdapat data Asprak yang tidak sesuai dengan pertemuan ini.');

        DB::transaction(function () use ($attendanceIds, $attendances, $validated) {
            foreach ($attendanceIds as $attendanceId) {
                $attendances[$attendanceId]->update([
                    'status' => $validated['status_asprak'][$attendanceId],
                    'keterangan' => $validated['keterangan_asprak'][$attendanceId] ?? null,
                    'diabsen_oleh_dosen_id' => auth('dosen')->id(),
                ]);
            }
        });

        activity_log('simpan_absensi_asprak', 'Dosen menyimpan absensi Asprak pertemuan ID: '.$pertemuan->pertemuan_praktik_id);

        return back()->with('success', 'Absensi Asprak berhasil disimpan untuk rekap kehadiran.');
    }

    public function updatePertemuan(Request $request, PertemuanPraktik $pertemuan)
    {
        $pertemuan->load('jadwal');
        $this->ensurePertemuanAktifMilikDosen($pertemuan);

        $validated = $request->validate([
            'tanggal_pertemuan' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode_pbm' => ['required', 'in:online,offline'],
            'topik' => ['required', 'string', 'max:255'],
            'sub_topik' => ['nullable', 'string', 'max:255'],
            'asprak_selection_present' => ['nullable', 'boolean'],
            'asprak_ids' => ['nullable', 'array'],
            'asprak_ids.*' => ['integer', 'distinct'],
        ]);

        $shouldSyncAsprak = $request->boolean('asprak_selection_present');
        $asprakIds = $shouldSyncAsprak
            ? $this->validatedAsprakAssignmentIds($pertemuan->jadwal, $validated['asprak_ids'] ?? [], false)
            : collect();
        $meetingData = collect($validated)->except(['asprak_ids', 'asprak_selection_present'])->all();
        DB::transaction(function () use ($pertemuan, $meetingData, $asprakIds, $shouldSyncAsprak) {
            $pertemuan->update($meetingData);
            $pertemuan->absensi()->update(['tanggal' => $meetingData['tanggal_pertemuan']]);
            if ($shouldSyncAsprak) {
                $this->syncAsprakMeeting($pertemuan, $asprakIds);
            }
        });

        activity_log('ubah_pertemuan_praktik', 'Dosen mengubah pertemuan praktik: '.$pertemuan->topik);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pertemuan praktik berhasil diperbarui.']);
        }

        return redirect()->route('dosen.absensi-praktik.index')
            ->with('success', 'Pertemuan praktik berhasil diperbarui.');
    }

    public function destroyPertemuan(Request $request, PertemuanPraktik $pertemuan)
    {
        $pertemuan->load('jadwal');
        $this->ensurePertemuanAktifMilikDosen($pertemuan);

        $identitasPertemuan = $pertemuan->tanggal_pertemuan?->format('Y-m-d').' - '.$pertemuan->topik;
        $jumlahAbsensi = $pertemuan->absensi()->count();

        DB::transaction(function () use ($pertemuan) {
            $pertemuan->absensi()->delete();
            $pertemuan->delete();
        });

        activity_log(
            'hapus_pertemuan_praktik',
            'Dosen menghapus pertemuan praktik '.$identitasPertemuan.' beserta '.$jumlahAbsensi.' data absensi'
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pertemuan praktik dan data absensi terkait berhasil dihapus.']);
        }

        return redirect()->route('dosen.absensi-praktik.index')
            ->with('success', 'Pertemuan praktik dan data absensi terkait berhasil dihapus.');
    }

    private function jadwalDosenQuery(): Builder
    {
        $dosen = auth('dosen')->user();

        return JadwalPraktik::query()->whereHas('kurikulum.dosenToMatakuliah', fn (Builder $query) => $query
            ->where('dosen_id', $dosen->dosen_id)
            ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
            ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal_praktik.jenis_kelas)'));
    }

    private function findJadwalDosenOrFail(int $jadwalId): JadwalPraktik
    {
        return $this->jadwalDosenQuery()->findOrFail($jadwalId);
    }

    private function ensureJadwalAktif(JadwalPraktik $jadwal): void
    {
        $activeTaId = (int) TahunAkademik::where('status_ta', 1)->value('ta_id');
        abort_unless($activeTaId > 0 && (int) $jadwal->ta_id === $activeTaId, 403, 'Asprak hanya dapat diatur pada tahun akademik aktif.');
    }

    private function validatedAsprakAssignmentIds(JadwalPraktik $jadwal, array $ids, bool $onlyActive): Collection
    {
        $requested = collect($ids)->map(fn ($id) => (int) $id)->unique()->values();
        $query = $jadwal->asprakAssignments()->whereIn('id', $requested);
        if ($onlyActive) {
            $query->where('aktif', true);
        }
        $valid = $query->pluck('id')->map(fn ($id) => (int) $id);
        abort_unless($valid->count() === $requested->count(), 422, 'Pilihan Asprak tidak valid atau tidak aktif pada mata kuliah ini.');

        return $valid;
    }

    private function syncAsprakMeeting(PertemuanPraktik $pertemuan, Collection $assignmentIds): void
    {
        $existing = $pertemuan->asprakAttendances()->get();
        $removed = $existing->whereNotIn('asprak_penugasan_id', $assignmentIds);
        abort_if(
            $removed->contains(fn (AsprakAttendance $attendance) => $attendance->status !== 'belum diabsen'),
            422,
            'Asprak yang sudah diabsen tidak dapat dilepas dari pertemuan. Ubah hanya Asprak yang belum diabsen.'
        );
        $pertemuan->asprakAttendances()
            ->whereIn('id', $removed->pluck('id'))
            ->delete();
        foreach ($assignmentIds as $assignmentId) {
            $pertemuan->asprakAttendances()->firstOrCreate(
                ['asprak_penugasan_id' => $assignmentId],
                ['status' => 'belum diabsen']
            );
        }
    }

    private function ensurePertemuanMilikDosen(PertemuanPraktik $pertemuan): void
    {
        abort_unless(
            (string) $pertemuan->dosen_id === (string) auth('dosen')->id()
                && $this->jadwalDosenQuery()->whereKey($pertemuan->jadwal_praktik_id)->exists(),
            403
        );
    }

    private function ensurePertemuanAktifMilikDosen(PertemuanPraktik $pertemuan): void
    {
        $this->ensurePertemuanMilikDosen($pertemuan);

        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        abort_unless($activeTaId, 422, 'Tidak ada Tahun Akademik aktif.');
        abort_unless(
            (int) $pertemuan->jadwal?->ta_id === (int) $activeTaId,
            403,
            'Pertemuan praktik tahun akademik sebelumnya tidak dapat diubah.'
        );
    }

    private function krsJadwal(JadwalPraktik $jadwal)
    {
        $jadwal->loadMissing('tahunAjaran');
        $periodeAkademik = strtolower((string) $jadwal->tahunAjaran?->semester);
        $taAktifId = (int) TahunAkademik::where('status_ta', 1)->value('ta_id');

        return Krs::with('mahasiswa')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->get()
            ->filter(function (Krs $krs) use ($jadwal, $periodeAkademik, $taAktifId) {
                $mahasiswa = $krs->mahasiswa;
                if (! $mahasiswa || strtolower((string) $mahasiswa->status_mhs) !== 'aktif') {
                    return false;
                }

                if ((int) $jadwal->ta_id === $taAktifId) {
                    $semester = (int) $mahasiswa->semester;
                    if ($periodeAkademik === 'ganjil' && ! in_array($semester, [1, 3, 5, 7], true)) {
                        return false;
                    }
                    if ($periodeAkademik === 'genap' && ! in_array($semester, [2, 4, 6, 8], true)) {
                        return false;
                    }
                }

                return KrsClassResolver::forKrs($krs, $mahasiswa)
                    === KrsClassResolver::normalize($jadwal->jenis_kelas);
            })
            ->values();
    }

    private function pesertaDisetujuiIds(JadwalPraktik $jadwal)
    {
        return $this->krsJadwal($jadwal)
            ->filter(fn (Krs $krs) => $krs->disetujui_pada !== null)
            ->pluck('mahasiswa_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function daftarPesertaAbsensi(JadwalPraktik $jadwal)
    {
        $jadwal->loadMissing(['kurikulum.mataKuliah', 'tahunAjaran']);
        $krsPerMahasiswa = $this->krsJadwal($jadwal)->groupBy('mahasiswa_id');
        $mahasiswaKrsIds = $krsPerMahasiswa->keys()->map(fn ($id) => (int) $id);
        $semesterMatkul = (int) ($jadwal->kurikulum?->mataKuliah?->smt
            ?: $jadwal->kurikulum?->mataKuliah?->semester);
        $kelasJadwal = KrsClassResolver::normalize($jadwal->jenis_kelas);
        $periodeAkademik = strtolower((string) $jadwal->tahunAjaran?->semester);
        $taAktifId = (int) TahunAkademik::where('status_ta', 1)->value('ta_id');

        return Mahasiswa::query()
            ->whereRaw('LOWER(status_mhs) = ?', ['aktif'])
            ->where('jurusan_id', $jadwal->jurusan_id ?: $jadwal->kurikulum?->jurusan_id)
            ->get()
            ->filter(function (Mahasiswa $mahasiswa) use (
                $jadwal,
                $mahasiswaKrsIds,
                $semesterMatkul,
                $kelasJadwal,
                $periodeAkademik,
                $taAktifId
            ) {
                if ((int) $jadwal->ta_id === $taAktifId) {
                    $semester = (int) $mahasiswa->semester;
                    if ($periodeAkademik === 'ganjil' && ! in_array($semester, [1, 3, 5, 7], true)) {
                        return false;
                    }
                    if ($periodeAkademik === 'genap' && ! in_array($semester, [2, 4, 6, 8], true)) {
                        return false;
                    }
                }

                if ($mahasiswaKrsIds->contains((int) $mahasiswa->mahasiswa_id)) {
                    return true;
                }

                return (int) $mahasiswa->semester === $semesterMatkul
                    && KrsClassResolver::normalize($mahasiswa->kelas) === $kelasJadwal;
            })
            ->map(function (Mahasiswa $mahasiswa) use ($krsPerMahasiswa) {
                $krs = $krsPerMahasiswa->get($mahasiswa->mahasiswa_id, collect());
                $disetujui = $krs->contains(fn (Krs $item) => $item->disetujui_pada !== null);

                return [
                    'mahasiswa' => $mahasiswa,
                    'status_krs' => $krs->isEmpty() ? 'belum' : ($disetujui ? 'disetujui' : 'menunggu'),
                    'boleh_diabsen' => $disetujui,
                ];
            })
            ->sortBy(fn (array $peserta) => strtolower((string) $peserta['mahasiswa']->nama))
            ->values();
    }

    private function syncPesertaDisetujui(PertemuanPraktik $pertemuan): void
    {
        foreach ($this->pesertaDisetujuiIds($pertemuan->jadwal) as $mahasiswaId) {
            AbsensiPraktik::firstOrCreate(
                [
                    'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
                    'mahasiswa_id' => $mahasiswaId,
                ],
                [
                    'jadwal_praktik_id' => $pertemuan->jadwal_praktik_id,
                    'tanggal' => $pertemuan->tanggal_pertemuan,
                    'status' => 'belum diabsen',
                    'keterangan' => null,
                ]
            );
        }
    }
}
