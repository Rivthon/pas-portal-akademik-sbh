<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\TagihanMahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
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
        if (! $activeTA) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Tidak ada Tahun Ajaran yang aktif.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Ambil SEMUA tagihan berdasarkan mahasiswa yang login, diurutkan dari semester terbaru
        $pembayaran = TagihanMahasiswa::where('mahasiswa_id', $user->mahasiswa_id)
            ->with(['tahunAjaran', 'tenorPembayaran', 'transaksi'])
            ->orderBy('semester', 'desc')
            ->orderBy('jatuh_tempo', 'desc')
            ->get();

        // Hitung aggregat global untuk summary dashboard
        $totalTagihanGlobal = $pembayaran->sum('jumlah_tagihan');

        $totalDibayarGlobal = 0;
        foreach ($pembayaran as $tagihan) {
            $totalDibayarGlobal += $tagihan->transaksi->where('status_verifikasi', 'diterima')->sum('nominal_bayar');
        }

        $sisaGlobal = $totalTagihanGlobal - $totalDibayarGlobal;

        activity_log('lihat_administrasi', 'Mahasiswa melihat data administrasi dan tagihan');

        return view('mahasiswa.administrasi.index', compact('pembayaran', 'totalTagihanGlobal', 'totalDibayarGlobal', 'sisaGlobal'));
    }
}
