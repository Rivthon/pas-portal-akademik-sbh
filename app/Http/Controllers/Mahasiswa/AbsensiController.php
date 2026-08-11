<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Pertemuan;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    // Menampilkan halaman absensi untuk mahasiswa berdasarkan jadwal
    public function index($jadwalId)
    {
        $settings = Setting::first();
        $jadwal = Jadwal::with('mataKuliah')->findOrFail($jadwalId);
        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($this->isEnrolled($jadwal, $mahasiswa), 403);

        // Cari pertemuan aktif
        $pertemuan = Pertemuan::where(
            'jadwal_id',
            $jadwalId
        )
            ->whereDate('tanggal_pertemuan', Carbon::now('Asia/Jakarta'))
            ->where('status', 1)
            ->first();

        // Jika tidak ada pertemuan aktif
        if (! $pertemuan) {
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

        return view('mahasiswa.absensi', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'pertemuan' => $pertemuan,
            'absensiHariIni' => $absensiHariIni,
            'absensi' => $absensi,
            'riwayatAbsensi' => $riwayatAbsensi,
            'settings' => $settings,
        ]);
    }

    public function riwayat($jadwalId)
    {
        $jadwal = Jadwal::with([
            'mataKuliah',
            'programStudi',
            'dosen',
        ])->findOrFail($jadwalId);

        $mahasiswa = Auth::guard('mahasiswa')->user();
        $pertemuan = Pertemuan::where('jadwal_id', $jadwalId)
            ->with([
                'absensi' => function ($query) use ($mahasiswa) {
                    $query->where('mahasiswa_id', $mahasiswa->mahasiswa_id);
                },
            ])
            ->orderBy('pertemuan_id')
            ->get();

        $riwayat = Absensi::where('jadwal_id', $jadwalId)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->orderBy('pertemuan_id')
            ->get();
        $hadir = $riwayat->where('status', 'hadir')->count();

        $izin = $riwayat->where('status', 'izin')->count();

        $sakit = $riwayat->where('status', 'sakit')->count();

        $alpha = $riwayat->where('status', 'tidak hadir')->count();
        $total = $riwayat->count();

        $persentase = $total
            ? round(($hadir / $total) * 100)
            : 0;

        return view(
            'mahasiswa.riwayat-absensi',
            compact(
                'jadwal',
                'riwayat',
                'hadir',
                'izin',
                'sakit',
                'alpha',
                'persentase'
            )
        );
    }
    // Menyimpan data absensi
    // public function show(Jadwal $jadwal)
    // {
    //     $mahasiswa = Auth::guard('mahasiswa')->user();
    //     $riwayatAbsensi = Absensi::where('jadwal_id', $jadwal->id)
    //         ->where('mahasiswa_id', $mahasiswa->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('mahasiswa.absensi', compact(
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
        $validatedData = $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,pertemuan_id',
            'jadwal_id' => 'required|exists:jadwal,id',
            'status' => 'required|string|in:hadir,tidak hadir,izin',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $mahasiswa = Auth::guard('mahasiswa')->user();
        $jadwal = Jadwal::findOrFail($validatedData['jadwal_id']);
        abort_unless($this->isEnrolled($jadwal, $mahasiswa), 403);

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $pertemuan = Pertemuan::whereKey($validatedData['pertemuan_id'])
            ->where('jadwal_id', $jadwal->getKey())
            ->whereDate('tanggal_pertemuan', $today)
            ->where('status', 1)
            ->firstOrFail();

        $created = DB::transaction(function () use ($validatedData, $mahasiswa, $jadwal, $pertemuan, $today) {
            $existing = Absensi::where('pertemuan_id', $pertemuan->getKey())
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->lockForUpdate()
                ->exists();

            if ($existing) {
                return false;
            }

            Absensi::create([
                'pertemuan_id' => $pertemuan->getKey(),
                'jadwal_id' => $jadwal->getKey(),
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'status' => $validatedData['status'],
                'keterangan' => $validatedData['keterangan'] ?? null,
                'tanggal' => $today,
            ]);

            return true;
        });

        if (! $created) {
            return redirect()->back()->with('error', 'Anda sudah melakukan absensi untuk pertemuan ini.');
        }

        activity_log('isi_absensi', 'Mahasiswa mengisi absensi: '.$validatedData['status']);

        return redirect()->back()->with('success', 'Absensi berhasil dikirim.');
    }

    private function isEnrolled(Jadwal $jadwal, $mahasiswa): bool
    {
        if (! $mahasiswa) {
            return false;
        }

        $kelasMahasiswa = strtolower((string) $mahasiswa->kelas) === 'karyawan'
            ? 'karyawan'
            : 'reguler';

        return strtolower((string) $jadwal->jenis_kelas) === $kelasMahasiswa
            && Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('kurikulum_id', $jadwal->kurikulum_id)
                ->where('ta_id', $jadwal->ta_id)
                ->exists();
    }
}
