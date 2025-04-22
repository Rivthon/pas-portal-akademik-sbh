<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Jadwal;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class JadwalKuliahController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();

        // Ambil jadwal berdasarkan program studi mahasiswa
        $jadwalKuliah = Jadwal::where('program_studi_id', $mahasiswa->program_studi_id)
            ->orderBy('hari', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $statistikKehadiran = DB::table('absensi')
            ->join('jadwal', 'absensi.jadwal_id', '=', 'jadwal.jadwal_id') // Tabel jadwal
            ->join('matakuliah', 'jadwal.matakuliah_id', '=', 'matakuliah.matakuliah_id') // Tabel matakuliah
            ->select(
                'jadwal.jadwal_id as jadwal_id',
                'matakuliah.name as matakuliah',
                'matakuliah.total_pertemuan', // Ambil total_pertemuan dari tabel matakuliah
                DB::raw('COUNT(CASE WHEN absensi.status = "hadir" THEN 1 END) as total_hadir'),
                DB::raw('(matakuliah.total_pertemuan - COUNT(CASE WHEN absensi.status = "hadir" THEN 1 END)) as total_tidak_hadir')
            )
            ->where('absensi.mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->groupBy('jadwal.jadwal_id', 'matakuliah.name', 'matakuliah.total_pertemuan')
            ->get();

        return view('mhs.jadwal.index', compact('jadwalKuliah', 'statistikKehadiran', 'settings'));
    }
}