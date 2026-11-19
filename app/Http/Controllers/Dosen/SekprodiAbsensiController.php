<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SekprodiAbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'program_studi_id' => ['nullable', 'string'],
            'ta_id' => ['nullable', 'integer', 'exists:tahun_ajaran,ta_id'],
            'jenis' => ['nullable', Rule::in(['teori', 'praktik'])],
            'dosen_id' => ['nullable', 'integer', 'exists:dosen,dosen_id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $dosen = $request->user('dosen');
        $managedPrograms = $dosen->programStudiSebagaiSekprodi()->orderBy('nama')->get();
        abort_if($managedPrograms->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Sekprodi.');

        $managedIds = $managedPrograms->pluck('jurusan_id')->map(fn ($id) => (string) $id);
        $programStudiId = (string) ($data['program_studi_id'] ?? $managedPrograms->first()->jurusan_id);
        abort_unless($managedIds->contains($programStudiId), 403, 'Anda bukan Sekprodi program studi ini.');

        $activeTaId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        $taId = (int) ($data['ta_id'] ?? $activeTaId);
        $jenis = $data['jenis'] ?? 'teori';
        $search = trim((string) ($data['search'] ?? ''));
        $dosenId = isset($data['dosen_id']) ? (int) $data['dosen_id'] : null;
        $table = $jenis === 'praktik' ? 'jadwal_praktik' : 'jadwal';
        $model = $jenis === 'praktik' ? JadwalPraktik::class : Jadwal::class;

        $jadwals = $model::query()
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.dosenToMatakuliah.dosen',
                'programStudi',
                'tahunAjaran',
                'ruangan',
            ])
            ->withCount('pertemuan')
            ->where("{$table}.jurusan_id", $programStudiId)
            ->when($taId, fn (Builder $query) => $query->where("{$table}.ta_id", $taId))
            ->when($dosenId, fn (Builder $query) => $query->whereHas(
                'kurikulum.dosenToMatakuliah',
                fn (Builder $assignment) => $assignment
                    ->where('dosen_id', $dosenId)
                    ->whereRaw('LOWER(jenis_dosen) = ?', [$jenis])
                    ->whereColumn('dosen_mata_kuliah.jenis_kelas', "{$table}.jenis_kelas")
            ))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $filter) use ($search) {
                $filter->whereHas('kurikulum.mataKuliah', fn (Builder $mataKuliah) => $mataKuliah
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('matakuliah_id', 'like', '%'.$search.'%'))
                    ->orWhereHas('kurikulum.dosenToMatakuliah.dosen', fn (Builder $pengajar) => $pengajar
                        ->where('nama', 'like', '%'.$search.'%'));
            }))
            ->orderByRaw("FIELD({$table}.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy("{$table}.jam_mulai")
            ->paginate(20)
            ->withQueryString();

        $jadwals->getCollection()->each(function ($jadwal) use ($jenis) {
            $kelas = KrsClassResolver::normalize($jadwal->jenis_kelas);
            $pengajar = $jadwal->kurikulum?->dosenToMatakuliah
                ?->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === $jenis
                    && KrsClassResolver::normalize($assignment->jenis_kelas) === $kelas)
                ->pluck('dosen.nama')
                ->filter()
                ->unique()
                ->values() ?? collect();

            $jadwal->setAttribute('nama_pengajar_sekprodi', $pengajar->join(', ') ?: 'Belum ditentukan');
        });

        $dosenPengajar = Dosen::query()
            ->whereHas('dosenMatakuliah', fn (Builder $assignment) => $assignment
                ->whereRaw('LOWER(jenis_dosen) = ?', [$jenis])
                ->whereHas('kurikulum', fn (Builder $kurikulum) => $kurikulum
                    ->where('jurusan_id', $programStudiId)
                    ->when($taId, fn (Builder $query) => $query->where('ta_id', $taId))))
            ->orderBy('nama')
            ->get(['dosen_id', 'nama']);

        activity_log('lihat_absensi_sekprodi', 'Sekprodi melihat rekap absensi prodi '.$programStudiId);

        return view('dosen.sekprodi.absensi', [
            'jadwals' => $jadwals,
            'managedPrograms' => $managedPrograms,
            'selectedProgramId' => $programStudiId,
            'tahunAkademik' => TahunAkademik::query()->orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
            'selectedJenis' => $jenis,
            'selectedDosenId' => $dosenId,
            'dosenPengajar' => $dosenPengajar,
            'search' => $search,
        ]);
    }

    public function show(Request $request, string $jenis, int $id): View
    {
        abort_unless(in_array($jenis, ['teori', 'praktik'], true), 404);

        $model = $jenis === 'praktik' ? JadwalPraktik::class : Jadwal::class;
        $jadwal = $model::query()
            ->with(['kurikulum.mataKuliah', 'programStudi', 'tahunAjaran', 'ruangan'])
            ->findOrFail($id);
        $this->ensureManagedProgram($request, $jadwal->jurusan_id);

        $pertemuan = $jadwal->pertemuan()
            ->with('dosen')
            ->withCount([
                'absensi as hadir' => fn ($query) => $query->whereIn('status', ['H', 'Hadir', 'hadir']),
                'absensi as sakit' => fn ($query) => $query->whereIn('status', ['S', 'Sakit', 'sakit']),
                'absensi as izin' => fn ($query) => $query->whereIn('status', ['I', 'Izin', 'izin']),
                'absensi as alfa' => fn ($query) => $query->whereIn('status', ['A', 'T', 'Alpha', 'Alpa', 'Alfa', 'alpha', 'alpa', 'alfa']),
            ])
            ->orderBy('tanggal_pertemuan')
            ->orderBy('jam_mulai')
            ->get();

        activity_log('lihat_detail_absensi_sekprodi', 'Sekprodi melihat detail absensi '.$jenis.' ID '.$id);

        return view('dosen.sekprodi.absensi-detail', compact('jadwal', 'pertemuan', 'jenis'));
    }

    public function mahasiswa(Request $request): View
    {
        $data = $request->validate([
            'program_studi_id' => ['nullable', 'string'],
            'ta_id' => ['nullable', 'integer', 'exists:tahun_ajaran,ta_id'],
            'mahasiswa_id' => ['nullable', 'integer', 'exists:mahasiswa,mahasiswa_id'],
        ]);

        [$managedPrograms, $programStudiId] = $this->managedProgramSelection(
            $request,
            $data['program_studi_id'] ?? null
        );

        $activeTaId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        $taId = (int) ($data['ta_id'] ?? $activeTaId);
        $mahasiswaList = Mahasiswa::query()
            ->where('jurusan_id', $programStudiId)
            ->orderBy('nama')
            ->get(['mahasiswa_id', 'nim', 'nama', 'semester', 'kelas', 'status_mhs']);

        $selectedMahasiswa = null;
        $rekap = collect();
        $ringkasan = $this->emptySummary();
        if (! empty($data['mahasiswa_id'])) {
            $selectedMahasiswa = Mahasiswa::query()
                ->with('programStudi')
                ->where('jurusan_id', $programStudiId)
                ->findOrFail((int) $data['mahasiswa_id']);
            [$rekap, $ringkasan] = $this->studentAttendanceReport($selectedMahasiswa, $taId);

            activity_log(
                'lihat_absensi_mahasiswa_sekprodi',
                'Sekprodi melihat absensi mahasiswa '.$selectedMahasiswa->nim.' tahun akademik '.$taId
            );
        }

        return view('dosen.sekprodi.absensi-mahasiswa', [
            'managedPrograms' => $managedPrograms,
            'selectedProgramId' => $programStudiId,
            'tahunAkademik' => TahunAkademik::query()->orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
            'mahasiswaList' => $mahasiswaList,
            'selectedMahasiswa' => $selectedMahasiswa,
            'rekap' => $rekap,
            'ringkasan' => $ringkasan,
        ]);
    }

    public function downloadMahasiswa(Request $request)
    {
        $data = $request->validate([
            'program_studi_id' => ['required', 'string'],
            'ta_id' => ['required', 'integer', 'exists:tahun_ajaran,ta_id'],
            'mahasiswa_id' => ['required', 'integer', 'exists:mahasiswa,mahasiswa_id'],
        ]);

        [, $programStudiId] = $this->managedProgramSelection($request, $data['program_studi_id']);
        $mahasiswa = Mahasiswa::query()
            ->with('programStudi')
            ->where('jurusan_id', $programStudiId)
            ->findOrFail((int) $data['mahasiswa_id']);
        $tahunAkademik = TahunAkademik::findOrFail((int) $data['ta_id']);
        [$rekap, $ringkasan] = $this->studentAttendanceReport($mahasiswa, (int) $data['ta_id']);

        activity_log(
            'download_absensi_mahasiswa_sekprodi',
            'Sekprodi mengunduh absensi mahasiswa '.$mahasiswa->nim.' tahun akademik '.$data['ta_id']
        );

        return Pdf::loadView('dosen.sekprodi.absensi-mahasiswa-pdf', compact(
            'mahasiswa', 'tahunAkademik', 'rekap', 'ringkasan'
        ))->setPaper('a4', 'landscape')
            ->download('rekap-absensi-'.$mahasiswa->nim.'-'.str($tahunAkademik->nama)->slug().'.pdf');
    }

    private function studentAttendanceReport(Mahasiswa $mahasiswa, int $taId): array
    {
        $krs = Krs::query()
            ->with('kurikulum.mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $taId)
            ->whereNotNull('disetujui_pada')
            ->get();
        $kurikulumIds = $krs->pluck('kurikulum_id')->filter()->unique()->values();

        $teori = Jadwal::query()
            ->with(['pertemuan' => fn ($query) => $query
                ->with(['absensi' => fn ($absensi) => $absensi
                    ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                ->orderBy('tanggal_pertemuan')->orderBy('jam_mulai')])
            ->where('ta_id', $taId)
            ->where('jurusan_id', $mahasiswa->jurusan_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->get()
            ->groupBy('kurikulum_id');
        $praktik = JadwalPraktik::query()
            ->with(['pertemuan' => fn ($query) => $query
                ->with(['absensi' => fn ($absensi) => $absensi
                    ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                ->orderBy('tanggal_pertemuan')->orderBy('jam_mulai')])
            ->where('ta_id', $taId)
            ->where('jurusan_id', $mahasiswa->jurusan_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->get()
            ->groupBy('kurikulum_id');

        $rekap = $krs->map(function (Krs $item) use ($mahasiswa, $teori, $praktik) {
            $jadwalTeori = $teori->get($item->kurikulum_id, collect())
                ->filter(fn (Jadwal $jadwal) => KrsClassResolver::matches($item, $jadwal, $mahasiswa));
            $jadwalPraktik = $praktik->get($item->kurikulum_id, collect())
                ->filter(fn (JadwalPraktik $jadwal) =>
                    (int) $item->kurikulum_id === (int) $jadwal->kurikulum_id
                    && (int) $item->ta_id === (int) $jadwal->ta_id
                    && KrsClassResolver::forKrs($item, $mahasiswa) === KrsClassResolver::normalize($jadwal->jenis_kelas)
                );
            $pertemuan = $this->studentMeetings($jadwalTeori, 'Teori')
                ->concat($this->studentMeetings($jadwalPraktik, 'Praktik'))
                ->sortBy([['tanggal', 'asc'], ['jam_mulai', 'asc']])
                ->values();
            $summary = $this->summarizeMeetings($pertemuan);

            return (object) array_merge($summary, [
                'krs_id' => $item->krs_id,
                'matakuliah_id' => $item->kurikulum?->mataKuliah?->matakuliah_id ?? $item->matakuliah_id,
                'nama_matakuliah' => $item->kurikulum?->mataKuliah?->nama ?? '-',
                'sks' => $item->kurikulum?->mataKuliah?->sks ?? '-',
                'semester' => $item->kurikulum?->mataKuliah?->smt ?? '-',
                'kelas' => jenis_kelas_label(KrsClassResolver::forKrs($item, $mahasiswa)),
                'pertemuan' => $pertemuan,
            ]);
        })->sortBy('nama_matakuliah')->values();

        return [$rekap, $this->summarizeCourses($rekap)];
    }

    private function studentMeetings(Collection $jadwals, string $jenis): Collection
    {
        return $jadwals->flatMap(fn ($jadwal) => $jadwal->pertemuan->map(function ($pertemuan) use ($jenis) {
            $absensi = $pertemuan->absensi->first();
            $status = $this->normalizeStatus($absensi?->status);

            return (object) [
                'jenis' => $jenis,
                'tanggal' => $pertemuan->tanggal_pertemuan,
                'jam_mulai' => $pertemuan->jam_mulai,
                'topik' => $pertemuan->topik ?: '-',
                'status' => $status['label'],
                'kode_status' => $status['code'],
                'keterangan' => $absensi?->keterangan,
            ];
        }));
    }

    private function summarizeMeetings(Collection $pertemuan): array
    {
        $counts = $pertemuan->countBy('kode_status');
        $tercatat = $pertemuan->whereIn('kode_status', ['H', 'S', 'I', 'A'])->count();
        $hadir = (int) $counts->get('H', 0);

        return [
            'hadir' => $hadir,
            'sakit' => (int) $counts->get('S', 0),
            'izin' => (int) $counts->get('I', 0),
            'alpha' => (int) $counts->get('A', 0),
            'belum_diabsen' => (int) $counts->get('-', 0),
            'total_pertemuan' => $pertemuan->count(),
            'persentase' => $tercatat > 0 ? round(($hadir / $tercatat) * 100, 1) : 0,
        ];
    }

    private function summarizeCourses(Collection $rekap): array
    {
        $hadir = (int) $rekap->sum('hadir');
        $tercatat = (int) ($rekap->sum('hadir') + $rekap->sum('sakit') + $rekap->sum('izin') + $rekap->sum('alpha'));

        return [
            'mata_kuliah' => $rekap->count(),
            'hadir' => $hadir,
            'sakit' => (int) $rekap->sum('sakit'),
            'izin' => (int) $rekap->sum('izin'),
            'alpha' => (int) $rekap->sum('alpha'),
            'belum_diabsen' => (int) $rekap->sum('belum_diabsen'),
            'total_pertemuan' => (int) $rekap->sum('total_pertemuan'),
            'persentase' => $tercatat > 0 ? round(($hadir / $tercatat) * 100, 1) : 0,
        ];
    }

    private function emptySummary(): array
    {
        return [
            'mata_kuliah' => 0, 'hadir' => 0, 'sakit' => 0, 'izin' => 0,
            'alpha' => 0, 'belum_diabsen' => 0, 'total_pertemuan' => 0, 'persentase' => 0,
        ];
    }

    private function normalizeStatus(?string $status): array
    {
        return match (strtolower(trim((string) $status))) {
            'h', 'hadir' => ['code' => 'H', 'label' => 'Hadir'],
            's', 'sakit' => ['code' => 'S', 'label' => 'Sakit'],
            'i', 'izin' => ['code' => 'I', 'label' => 'Izin'],
            'a', 't', 'tidak hadir', 'alpha', 'alpa', 'alfa' => ['code' => 'A', 'label' => 'Alpha'],
            default => ['code' => '-', 'label' => 'Belum Diabsen'],
        };
    }

    private function managedProgramSelection(Request $request, $requestedId): array
    {
        $managedPrograms = $request->user('dosen')->programStudiSebagaiSekprodi()->orderBy('nama')->get();
        abort_if($managedPrograms->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Sekprodi.');

        $programStudiId = (string) ($requestedId ?? $managedPrograms->first()->jurusan_id);
        abort_unless(
            $managedPrograms->pluck('jurusan_id')->map(fn ($id) => (string) $id)->contains($programStudiId),
            403,
            'Anda bukan Sekprodi program studi ini.'
        );

        return [$managedPrograms, $programStudiId];
    }

    private function ensureManagedProgram(Request $request, $programStudiId): void
    {
        abort_unless(
            $request->user('dosen')->programStudiSebagaiSekprodi()
                ->where('program_studi.jurusan_id', $programStudiId)
                ->exists(),
            403,
            'Data absensi ini bukan milik program studi yang Anda kelola.'
        );
    }
}
