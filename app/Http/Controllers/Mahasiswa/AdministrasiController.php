<?php

namespace App\Http\Controllers\Mahasiswa;

use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\TagihanMahasiswa;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class AdministrasiController extends Controller
{
     public function index(Request $request)
    {
        $user = Auth::guard('mahasiswa')->user(); // Ambil user mahasiswa yang sedang login

        // Ambil semester dan program studi mahasiswa yang login
        $semester = $user->semester;
        $prodi = $user->jurusan_id;

        // Ambil Tahun Akademik yang sedang aktif
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        // Jika tidak ada Tahun Akademik yang aktif, kembalikan error
        if (!$activeTA) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Ambil tagihan berdasarkan mahasiswa yang login, semester, dan Tahun Akademik aktif
        $pembayaran = TagihanMahasiswa::whereHas('mahasiswa', function ($query) use ($user) {
            $query->where('mahasiswa_id', $user->mahasiswa_id);
        })
        ->where('semester', $semester)
        ->with(['mahasiswa', 'tahunAjaran', 'tenorPembayaran'])
        ->get();

        return view('students.administrasi.index', compact('pembayaran'));
    }

}