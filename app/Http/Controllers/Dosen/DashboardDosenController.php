<?php

namespace App\Http\Controllers\Dosen;

use Carbon\Carbon;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\CalendarAkademik;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\DosenMatakuliah;
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use Illuminate\Support\Facades\DB;

class DashboardDosenController extends Controller
{
        public function index()
        {
            $dosen = auth('dosen')->user();
            $settings = Setting::first();
            $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
            $kalenderAkademik = CalendarAkademik::where('jurusan_id', $dosen->jurusan_id)->get();
            $tanggalSekarang = Carbon::now()->translatedFormat('l, d F Y');
            
            // Hitung statistik dosen
            $totalMatakuliah = DosenMatakuliah::where('dosen_id', $dosen->dosen_id)
                                ->distinct('kurikulum_id')->count();
            
            $totalMahasiswaBimbingan = Mahasiswa::where('dosen_id', $dosen->dosen_id)
                                        ->where('status_mhs', 'aktif')->count();
                                        
            $rataRataEdomRaw = Penilaian::where('dosen_id', $dosen->dosen_id)->avg(\DB::raw('CAST(nilai AS UNSIGNED)'));
            $rataRataEdom = $rataRataEdomRaw ? round($rataRataEdomRaw, 2) : 0;

            return view('dosen.dashboard', compact(
                'tanggalSekarang',
                'settings',
                'ta',
                'kalenderAkademik',
                'totalMatakuliah',
                'totalMahasiswaBimbingan',
                'rataRataEdom'
            ));
        }

        public function hasilEdom()
        {
            $dosen = auth('dosen')->user();
            
            // EDOM summary
            $hasilEdom = DB::table('penilaian')
                ->join('kurikulum', 'penilaian.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->where('penilaian.dosen_id', $dosen->dosen_id)
                ->select(
                    'kurikulum.kurikulum_id',
                    'matakuliah.matakuliah_id',
                    'matakuliah.nama',
                    'matakuliah.smt',
                    DB::raw('AVG(CAST(penilaian.nilai AS UNSIGNED)) as rata_rata_nilai'),
                    DB::raw('COUNT(DISTINCT penilaian.mahasiswa_id) as jumlah_pemberi_nilai')
                )
                ->groupBy('kurikulum.kurikulum_id', 'matakuliah.matakuliah_id', 'matakuliah.nama', 'matakuliah.smt')
                ->get();
                
            $rataRataKeseluruhan = DB::table('penilaian')
                ->where('dosen_id', $dosen->dosen_id)
                ->avg(DB::raw('CAST(nilai AS UNSIGNED)'));
                
            $rataRataKeseluruhan = $rataRataKeseluruhan ? round($rataRataKeseluruhan, 2) : 0;

            return view('dosen.edom.hasil', compact('hasilEdom', 'rataRataKeseluruhan'));
        }

    }