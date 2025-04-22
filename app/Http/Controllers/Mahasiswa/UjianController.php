<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Krs;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    public function tampikanNilaiUts()
    {
      $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        try {

            $nilai = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            return view('students.nilai-uts.index', compact('nilai','ta','mahasiswa'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: ' . $e->getMessage());
        }
    }
    public function tampikanNilaiUas()
    {
      $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        try {

            $nilai = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();
            return view('students.nilai-uas.index', compact('nilai','ta','mahasiswa'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: ' . $e->getMessage());
        }
    }
    public function tampikanNilaiAkhir()
    {
      $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        try {

            $nilai = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            return view('students.nilai-akhir.index', compact('nilai','ta','mahasiswa'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: ' . $e->getMessage());
        }
    }
}