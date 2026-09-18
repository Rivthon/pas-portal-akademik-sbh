<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Kurikulum;
use App\Models\ProgramStudi;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class JadwalKeseluruhanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'jurusan_id' => ['nullable', 'integer', 'exists:program_studi,jurusan_id'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
            'kelas' => ['nullable', 'in:reguler,karyawan'],
            'ruangan_id' => ['nullable', 'integer', 'exists:ruangan,ruangan_id'],
        ]);

        $tahunAjaran = TahunAkademik::query()
            ->where('status_ta', 1)
            ->firstOrFail(['ta_id', 'nama', 'semester']);

        $jadwalTeori = $this->jadwal($tahunAjaran->ta_id, $filters, Jadwal::query(), 'Teori', 'teori');
        $jadwalPraktik = $this->jadwal($tahunAjaran->ta_id, $filters, JadwalPraktik::query(), 'Praktik', 'praktik');
        $semuaJadwal = $jadwalTeori
            ->concat($jadwalPraktik)
            ->sortBy(fn ($item) => sprintf('%s|%s|%s', $item->hari ?? '', $item->jam_mulai ?? '', $item->nama_matakuliah))
            ->values();

        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwalPerHari = collect($hari)->mapWithKeys(fn ($namaHari) => [
            $namaHari => $semuaJadwal
                ->filter(fn ($item) => strcasecmp(trim((string) $item->hari), $namaHari) === 0)
                ->sortBy('jam_mulai')
                ->values(),
        ]);
        $belumDiatur = $semuaJadwal
            ->reject(fn ($item) => collect($hari)->contains(fn ($namaHari) => strcasecmp(trim((string) $item->hari), $namaHari) === 0))
            ->values();

        $programStudi = ProgramStudi::query()->orderBy('nama')->get(['jurusan_id', 'nama', 'singkat']);
        $ruangan = Ruangan::query()->orderBy('nama')->get(['ruangan_id', 'nama']);
        $semesterOptions = Kurikulum::with('mataKuliah:matakuliah_id,smt')
            ->where('ta_id', $tahunAjaran->ta_id)
            ->get()
            ->pluck('mataKuliah.smt')
            ->filter()
            ->map(fn ($semester) => (int) $semester)
            ->unique()
            ->sort()
            ->values();

        $statistik = [
            'total' => $semuaJadwal->count(),
            'teori' => $jadwalTeori->count(),
            'praktik' => $jadwalPraktik->count(),
            'belum_diatur' => $belumDiatur->count(),
        ];

        activity_log('lihat_jadwal_keseluruhan', 'Admin melihat jadwal perkuliahan keseluruhan');

        return view('admin.akademik.jadwal-keseluruhan.index', compact(
            'tahunAjaran',
            'jadwalPerHari',
            'belumDiatur',
            'programStudi',
            'ruangan',
            'semesterOptions',
            'filters',
            'statistik'
        ));
    }

    private function jadwal(
        int $taId,
        array $filters,
        Builder $query,
        string $jenisJadwal,
        string $jenisDosen
    ): Collection {
        return $query
            ->with([
                'kurikulum.mataKuliah:matakuliah_id,nama,smt,sks',
                'kurikulum.dosenToMatakuliah.dosen:dosen_id,nama',
                'programStudi:jurusan_id,nama,singkat',
                'ruangan:ruangan_id,nama',
            ])
            ->where('ta_id', $taId)
            ->when($filters['jurusan_id'] ?? null, fn ($q, $id) => $q->where('jurusan_id', $id))
            ->when($filters['semester'] ?? null, fn ($q, $semester) => $q->whereHas(
                'kurikulum.mataKuliah',
                fn ($mataKuliah) => $mataKuliah->where('smt', $semester)
            ))
            ->when($filters['kelas'] ?? null, fn ($q, $kelas) => $q->whereRaw(
                'LOWER(TRIM(jenis_kelas)) = ?',
                [$kelas]
            ))
            ->when($filters['ruangan_id'] ?? null, fn ($q, $id) => $q->where('ruangan_id', $id))
            ->get()
            ->map(function ($item) use ($jenisJadwal, $jenisDosen) {
                $kelasJadwal = $this->normalisasiKelas($item->jenis_kelas);
                $dosen = collect($item->kurikulum?->dosenToMatakuliah)
                    ->filter(function ($assignment) use ($jenisDosen, $kelasJadwal) {
                        if (strtolower(trim((string) $assignment->jenis_dosen)) !== $jenisDosen) {
                            return false;
                        }

                        $kelasDosen = $this->normalisasiKelas($assignment->jenis_kelas);

                        return $kelasDosen === $kelasJadwal
                            || ($jenisDosen === 'praktik' && blank($assignment->jenis_kelas));
                    })
                    ->pluck('dosen.nama')
                    ->filter()
                    ->unique()
                    ->values();

                $item->setAttribute('jenis_jadwal', $jenisJadwal);
                $item->setAttribute('nama_matakuliah', $item->kurikulum?->mataKuliah?->nama ?? 'Mata kuliah tidak tersedia');
                $item->setAttribute('dosen_pengampu', $dosen);
                $item->setAttribute('kelas_label', $kelasJadwal === 'karyawan' ? 'Reguler B' : 'Reguler A');

                return $item;
            });
    }

    private function normalisasiKelas(?string $kelas): string
    {
        return match (strtolower(trim((string) $kelas))) {
            'karyawan', 'reguler b' => 'karyawan',
            default => 'reguler',
        };
    }
}
