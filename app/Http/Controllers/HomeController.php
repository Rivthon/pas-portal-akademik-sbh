<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Data mahasiswa per tahun masuk (bar chart)
        $dataMahasiswa = DB::table('mahasiswa')
            ->select(DB::raw('tahun_masuk, COUNT(*) as total'))
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->get();

        $labels = $dataMahasiswa->pluck('tahun_masuk')->toArray();
        $values = $dataMahasiswa->pluck('total')->toArray();

        // Data mahasiswa per program studi (pie/doughnut chart)
        $programStudiData = ProgramStudi::whereHas('mahasiswa', function ($query) {
            $query->where('status_mhs', 'aktif');
        })->withCount([
                    'mahasiswa' => function ($query) {
                        $query->where('status_mhs', 'aktif');
                    }
                ])->get();

        $programStudiLabels = $programStudiData->pluck('nama')->toArray();
        $programStudiCounts = $programStudiData->pluck('mahasiswa_count')->toArray();
        $programStudiJurusanIds = $programStudiData->pluck('jurusan_id')->toArray();

        // Statistik mahasiswa berdasarkan status
        $totalMahasiswa = Mahasiswa::count();
        $totalMahasiswaAktif = Mahasiswa::where('status_mhs', 'aktif')->count();
        $totalMahasiswaTidakAktif = Mahasiswa::where('status_mhs', 'nonaktif')->count();
        $totalMahasiswaLulus = Mahasiswa::where('status_mhs', 'lulus')->count();
        $totalMahasiswaCuti = Mahasiswa::where('status_mhs', 'cuti')->count();

        // Tahun Akademik Aktif
        $tahunAkademikAktif = TahunAkademik::where('status_ta', 'aktif')->first();

        // Total dosen  
        $totalDosen = Dosen::count();

        // Total mata kuliah
        $totalMatakuliah = Matakuliah::count();

        // Jadwal hari ini
        $hariIni = strtolower(\Carbon\Carbon::now()->translatedFormat('l'));
        $jadwalHariIni = collect();
        if ($tahunAkademikAktif) {
            $jadwalHariIni = Jadwal::with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen', 'programStudi', 'ruangan'])
                ->where('ta_id', $tahunAkademikAktif->ta_id ?? $tahunAkademikAktif->id)
                ->where('hari', $hariIni)
                ->orderBy('jam_mulai', 'asc')
                ->limit(5)
                ->get();
        }

        return view('home', [
            'totalMahasiswa' => $totalMahasiswa,
            'totalMahasiswaAktif' => $totalMahasiswaAktif,
            'totalMahasiswaTidakAktif' => $totalMahasiswaTidakAktif,
            'totalMahasiswaLulus' => $totalMahasiswaLulus,
            'totalMahasiswaCuti' => $totalMahasiswaCuti,
            'totalDosen' => $totalDosen,
            'totalMatakuliah' => $totalMatakuliah,
            'tahunAkademikAktif' => $tahunAkademikAktif,
            'programStudiLabels' => $programStudiLabels,
            'programStudiCounts' => $programStudiCounts,
            'programStudiJurusanIds' => $programStudiJurusanIds,
            'labels' => $labels,
            'values' => $values,
            'jadwalHariIni' => $jadwalHariIni,
        ]);
    }

}
