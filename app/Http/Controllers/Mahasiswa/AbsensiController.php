<?php

namespace App\Http\Controllers\Mahasiswa;

use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Setting;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    // Menampilkan halaman absensi untuk mahasiswa berdasarkan jadwal
    public function index($jadwalId)
    {
        $settings = Setting::first();
        $jadwal = Jadwal::with('mataKuliah')->findOrFail($jadwalId);
        $mahasiswa = Auth::guard('mahasiswa')->user();

        // Cari pertemuan aktif
        $pertemuan = Pertemuan::where(
            'jadwal_id',
            $jadwalId
        )
        ->whereDate('tanggal_pertemuan', Carbon::now('Asia/Jakarta'))
        ->where('status', 1)
        ->first();

        // Jika tidak ada pertemuan aktif
        if (!$pertemuan) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Tidak ada sesi pertemuan yang aktif.');
        }
        $absensiHariIni = false;
        if ($pertemuan) {
            $absensiHariIni = Absensi::where('pertemuan_id', $pertemuan->pertemuan_id)
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->whereDate('tanggal', Carbon::now('Asia/Jakarta')) // Cek tanggal hari ini
                ->exists();
        }
        // Cari apakah mahasiswa sudah absen
        $absensi = Absensi::where('pertemuan_id', $pertemuan->pertemuan_id)
        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
        ->exists();

        // Riwayat absensi mahasiswa
        $riwayatAbsensi = Absensi::where('jadwal_id', $jadwalId)
        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mhs.absensi', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'pertemuan' => $pertemuan,
            'absensiHariIni' => $absensiHariIni,
            'absensi' => $absensi,
            'riwayatAbsensi' => $riwayatAbsensi,
            'settings' => $settings,
        ]);
    }

    // Menyimpan data absensi
    // public function show(Jadwal $jadwal)
    // {
    //     $mahasiswa = Auth::guard('mahasiswa')->user();
    //     $riwayatAbsensi = Absensi::where('jadwal_id', $jadwal->id)
    //         ->where('mahasiswa_id', $mahasiswa->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('mhs.absensi', compact(
    //         'jadwal',
    //         'mahasiswa',
    //         'riwayatAbsensi'
    //     ));
    // }



    // public function store(Request $request)
    // {
    //     // Validasi input
    //     $validatedData = $request->validate([
    //         'jadwal_id' => 'required|exists:jadwal,jadwal_id',
    //         'mahasiswa_id' => 'required|exists:mahasiswa,mahasiswa_id',
    //         'status' => 'required|string|max:255',
    //         'keterangan' => 'nullable|string|max:255',
    //     ]);

    //     // Periksa apakah mahasiswa sudah absen pada jadwal dan hari yang sama
    //     $hasAbsensiToday = Absensi::where('jadwal_id', $validatedData['jadwal_id'],)
    //     ->where('mahasiswa_id', $validatedData['mahasiswa_id'])
    //     ->whereDate(
    //         'tanggal',
    //         Carbon::now('Asia/Jakarta')
    //     )
    //     ->exists();

    //     if ($hasAbsensiToday) {
    //         return redirect()->back()->with('error', 'Anda sudah melakukan absensi hari ini.');
    //     }

    //     // Simpan absensi jika belum ada
    //     Absensi::create([
    //         'jadwal_id' => $validatedData['jadwal_id'],
    //         'mahasiswa_id' => $validatedData['mahasiswa_id'],
    //         'status' => $validatedData['status'],
    //         'keterangan' => $validatedData['keterangan'],
    //         'tanggal' => now('Asia/Jakarta'),
    //     ]);

    //     return redirect()->back()->with('success', 'Absensi berhasil dikirim.');
    // }
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate(['pertemuan_id' => 'required|exists:pertemuan,pertemuan_id',
            'jadwal_id' => 'required|exists:jadwal,jadwal_id', // Tambahkan validasi jadwal_id
            'mahasiswa_id' => 'required|exists:mahasiswa,mahasiswa_id',
            'status' => 'required|string|in:hadir,tidak hadir,izin',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Periksa apakah mahasiswa sudah absen pada pertemuan dan jadwal yang sama
        $hasAbsensi = Absensi::where('pertemuan_id', $validatedData['pertemuan_id'])
        ->where('jadwal_id', $validatedData['jadwal_id'])
            ->where('mahasiswa_id', $validatedData['mahasiswa_id'])
            ->whereDate('tanggal', Carbon::now('Asia/Jakarta'))
        ->exists();

        if ($hasAbsensi) {
            return redirect()->back()->with('error', 'Anda sudah melakukan absensi untuk jadwal dan pertemuan ini.');
        }

        // Simpan absensi jika belum ada
        Absensi::create(['pertemuan_id' => $validatedData['pertemuan_id'],
            'jadwal_id' => $validatedData['jadwal_id'], // Tambahkan jadwal_id ke data yang disimpan
            'mahasiswa_id' => $validatedData['mahasiswa_id'],
            'status' => $validatedData['status'],
            'keterangan' => $validatedData['keterangan'],
            'tanggal' => now('Asia/Jakarta'),
        ]);

        return redirect()->back()->with('success', 'Absensi berhasil dikirim.');
    }

}
