<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
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
       $dataMahasiswa = DB::table('mahasiswa')
        ->select(DB::raw('tahun_masuk, COUNT(*) as total','jurusan_id'))
        ->groupBy('tahun_masuk')
        ->orderBy('tahun_masuk', 'asc')
        ->get();

        $labels = $dataMahasiswa->pluck('tahun_masuk')->toArray();
        $values = $dataMahasiswa->pluck('total')->toArray();
        $programStudiJurusanIds = $dataMahasiswa->pluck('jurusan_id')->toArray(); // Ambil jurusan_id

        $programStudiData = ProgramStudi::whereHas('mahasiswa', function ($query) {
            $query->where('status_mhs', 'aktif');
        })->withCount(['mahasiswa' => function ($query) {
            $query->where('status_mhs', 'aktif');
        }])->get();

        $programStudiLabels = $programStudiData->pluck('nama')->toArray();
        $programStudiCounts = $programStudiData->pluck('mahasiswa_count')->toArray();

        // Menghitung total mahasiswa berdasarkan status
        $totalMahasiswaAktif = Mahasiswa::where('status_mhs', 'aktif')->count();
        $totalMahasiswaTidakAktif = Mahasiswa::where('status_mhs', 'nonaktif')->count();
        $totalMahasiswaLulus = Mahasiswa::where('status_mhs', 'lulus')->count();
        $totalMahasiswaCuti = Mahasiswa::where('status_mhs', 'cuti')->count();


      return view('home', [
            'totalMahasiswaAktif' => $totalMahasiswaAktif,
            'totalMahasiswaTidakAktif' => $totalMahasiswaTidakAktif,
            'totalMahasiswaLulus' => $totalMahasiswaLulus,
            'totalMahasiswaCuti' => $totalMahasiswaCuti,
            'programStudiLabels' => $programStudiLabels,
            'programStudiCounts' => $programStudiCounts,
            'labels' => $labels,
            'values' => $values,
            'programStudiJurusanIds' => $programStudiJurusanIds,
        ]);
    }

}