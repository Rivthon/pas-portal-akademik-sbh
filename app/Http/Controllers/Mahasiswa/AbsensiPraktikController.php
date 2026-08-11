<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalPraktik;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Builder;

class AbsensiPraktikController extends Controller
{
    public function index()
    {
        $mahasiswa = auth('mahasiswa')->user();
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $jadwal = collect();

        if ($activeTa) {
            $jadwal = $this->jadwalMahasiswaQuery($mahasiswa, $activeTa->ta_id)
                ->with(['kurikulum.mataKuliah', 'ruangan'])
                ->withCount('pertemuan')
                ->withCount(['pertemuan as hadir_count' => fn (Builder $query) => $query
                    ->whereHas('absensi', fn (Builder $absensi) => $absensi
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)->where('status', 'hadir'))])
                ->orderBy('hari')->orderBy('jam_mulai')->get();
        }

        return view('mahasiswa.absensi-praktik.index', compact('jadwal', 'activeTa'));
    }

    public function show(JadwalPraktik $jadwal)
    {
        $mahasiswa = auth('mahasiswa')->user();
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $bolehMelihat = $activeTa
            && $this->jadwalMahasiswaQuery($mahasiswa, $activeTa->ta_id)
                ->whereKey($jadwal->id)->exists();
        abort_unless($bolehMelihat, 403);

        $jadwal->load(['kurikulum.mataKuliah', 'ruangan']);
        $pertemuan = PertemuanPraktik::where('jadwal_praktik_id', $jadwal->id)
            ->whereHas('absensi', fn (Builder $query) => $query
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id))
            ->with(['absensi' => fn ($query) => $query->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
            ->orderBy('tanggal_pertemuan')->orderBy('jam_mulai')->get();
        $statistik = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'tidak hadir' => 0];
        foreach ($pertemuan as $item) {
            $status = $item->absensi->first()?->status;
            if (array_key_exists($status, $statistik)) {
                $statistik[$status]++;
            }
        }
        $total = array_sum($statistik);
        $persentase = $total > 0 ? round(($statistik['hadir'] / $total) * 100) : 0;

        return view('mahasiswa.absensi-praktik.show', compact('jadwal', 'pertemuan', 'statistik', 'persentase'));
    }

    private function jadwalMahasiswaQuery($mahasiswa, int $taId): Builder
    {
        $jenisKelas = strtolower((string) $mahasiswa->kelas);

        return JadwalPraktik::query()
            ->where('ta_id', $taId)
            ->whereHas('kurikulum.krs', fn (Builder $query) => $query
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $taId))
            ->whereHas('kurikulum.dosenToMatakuliah', fn (Builder $query) => $query
                ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
                ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal_praktik.jenis_kelas)'))
            ->when(in_array($jenisKelas, ['pagi', 'reguler'], true), fn (Builder $query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['reguler']))
            ->when($jenisKelas === 'karyawan', fn (Builder $query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['karyawan']));
    }
}
