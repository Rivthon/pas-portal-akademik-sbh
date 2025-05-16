<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SkpiController extends Controller
{
    public function index()
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        return view('students.skpi.index', compact('settings', 'mahasiswa', 'semester', 'prodi','ta'));
    }
}
