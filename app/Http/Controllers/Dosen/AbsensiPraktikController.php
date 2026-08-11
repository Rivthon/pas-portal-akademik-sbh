<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPraktik;
use App\Models\JadwalPraktik;
use App\Models\Mahasiswa;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use App\Services\JadwalPraktikAssignmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
                    'kurikulum.mataKuliah', 'programStudi', 'ruangan',
                    'pertemuan' => fn ($query) => $query
                        ->where('dosen_id', $dosen->dosen_id)
                        ->withCount('absensi')
                        ->orderByDesc('tanggal_pertemuan'),
                ])
                ->when($request->filled('search'), function (Builder $query) use ($request) {
                    $search = trim($request->string('search')->toString());
                    $query->whereHas('kurikulum.mataKuliah', fn (Builder $query) => $query
                        ->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('matakuliah_id', 'like', '%'.$search.'%'));
                })
                ->orderBy('hari')->orderBy('jam_mulai')->get();
        }

        return view('dosen.absensi-praktik.index', compact('jadwal', 'activeTa'));
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
        ]);

        $jadwal = $this->findJadwalDosenOrFail((int) $validated['jadwal_praktik_id']);
        $pertemuan = DB::transaction(function () use ($validated, $jadwal) {
            $pertemuan = PertemuanPraktik::create([...$validated, 'dosen_id' => auth('dosen')->id()]);
            $now = now();
            $rows = $this->mahasiswaJadwalQuery($jadwal)->pluck('mahasiswa_id')->map(fn ($mahasiswaId) => [
                'jadwal_praktik_id' => $jadwal->id,
                'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
                'mahasiswa_id' => $mahasiswaId,
                'tanggal' => $validated['tanggal_pertemuan'],
                'status' => 'tidak hadir',
                'keterangan' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            if ($rows->isNotEmpty()) {
                AbsensiPraktik::insert($rows->all());
            }

            return $pertemuan;
        });

        activity_log('buat_pertemuan_praktik', 'Dosen membuat pertemuan praktik ID: '.$pertemuan->pertemuan_praktik_id);

        return redirect()->route('dosen.absensi-praktik.show', $pertemuan)
            ->with('success', 'Pertemuan praktik berhasil dibuat. Silakan isi kehadiran mahasiswa.');
    }

    public function show(PertemuanPraktik $pertemuan)
    {
        $pertemuan->load(['jadwal.kurikulum.mataKuliah', 'jadwal.ruangan']);
        $this->ensurePertemuanMilikDosen($pertemuan);

        $absensi = $pertemuan->absensi()->with('mahasiswa')->get()
            ->sortBy(fn ($item) => $item->mahasiswa?->nama)->values();
        $mahasiswaTambahan = $this->mahasiswaJadwalQuery($pertemuan->jadwal)
            ->whereNotIn('mahasiswa_id', $absensi->pluck('mahasiswa_id'))
            ->orderBy('nama')->get(['mahasiswa_id', 'nim', 'nama', 'semester']);

        return view('dosen.absensi-praktik.show', compact('pertemuan', 'absensi', 'mahasiswaTambahan'));
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
        $validIds = $this->mahasiswaJadwalQuery($pertemuan->jadwal)
            ->whereIn('mahasiswa_id', $mahasiswaIds)->pluck('mahasiswa_id');
        abort_unless($validIds->count() === $mahasiswaIds->count(), 422, 'Terdapat mahasiswa yang tidak terdaftar pada kelas praktik ini.');

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

    private function ensurePertemuanMilikDosen(PertemuanPraktik $pertemuan): void
    {
        abort_unless(
            (string) $pertemuan->dosen_id === (string) auth('dosen')->id()
                && $this->jadwalDosenQuery()->whereKey($pertemuan->jadwal_praktik_id)->exists(),
            403
        );
    }

    private function mahasiswaJadwalQuery(JadwalPraktik $jadwal): Builder
    {
        $jenisKelas = strtolower((string) $jadwal->jenis_kelas);

        return Mahasiswa::query()->where('status_mhs', 'aktif')
            ->whereHas('krs', fn (Builder $query) => $query->where('kurikulum_id', $jadwal->kurikulum_id))
            ->when($jenisKelas === 'reguler', fn (Builder $query) => $query->whereIn('kelas', ['pagi', 'reguler']))
            ->when($jenisKelas === 'karyawan', fn (Builder $query) => $query->where('kelas', 'karyawan'));
    }
}
