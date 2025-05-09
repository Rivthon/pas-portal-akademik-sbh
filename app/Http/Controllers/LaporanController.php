<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Setting;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function index()
    {
        // Ambil daftar mata kuliah dari jadwal
        $mataKuliah = Jadwal::with('mataKuliah')->get();

        return view('laporan.index', [
            'mataKuliah' => $mataKuliah,
        ]);
    }


    public function generatePDF(Request $request)
    {
        // Validasi input
        $this->validate($request, [
            'jadwal_id' => 'required|exists:jadwal,jadwal_id',
        ]);

        // Ambil jadwal dengan relasi
        $jadwal = Jadwal::with(['mataKuliah', 'pertemuan.absensi'])->findOrFail($request->jadwal_id);

        // Total pertemuan terkait jadwal
        $totalPertemuan = $jadwal->pertemuan->count();

        // Rekap absensi berdasarkan pertemuan
        $rekapAbsensi = $jadwal->pertemuan->map(function ($pertemuan) {
            return [
                'topik' => $pertemuan->topik, // Topik pertemuan
                'tanggal' => $pertemuan->tanggal_pertemuan, // Tanggal pertemuan
                'absensi' => $pertemuan->absensi->groupBy('mahasiswa_id')->mapWithKeys(function ($absensiRecords, $mahasiswaId) {
                    // Ambil status pertama dari absensi mahasiswa untuk pertemuan ini
                    $status = $absensiRecords->first()->status ?? '-';
                    return [$mahasiswaId => $this->mapAbsensiStatus($status)];
                }),
            ];
        });

        // Ambil mahasiswa unik berdasarkan absensi
        $mahasiswa = $jadwal->pertemuan->flatMap(function ($pertemuan) {
            return $pertemuan->absensi->map(function ($absensi) {
                return $absensi->mahasiswa; // Ambil mahasiswa terkait absensi
            });
        })->unique('mahasiswa_id')->values(); // Hapus duplikat berdasarkan mahasiswa_id


        // Cek ketersediaan data
        if ($rekapAbsensi->isEmpty() || $mahasiswa->isEmpty()) {
            return back()->with('error', 'Data absensi tidak tersedia untuk jadwal ini.');
        }

        // Generate PDF
        return $this->generatePDFDocument($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan);
    }


private function mapAbsensiStatus($status)
{
    // Map attendance status to a concise format
    $statusMapping = [
        'hadir' => 'H',
        'tidak hadir' => 'T',
        'izin' => 'I',
        // Add more mappings if needed
    ];

    return $statusMapping[$status] ?? null; // Return null for unknown statuses
}

    public function generatePDFDocument($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan)
    {
        $settings = Setting::first();
        $websiteUrl = $settings->website_url ?? 'https://example.com';

        // Generate QR code
        $qrCode = new QrCode($websiteUrl);
        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);

        // Simpan QR code ke storage sementara
        $qrCodePath = 'temp/qr-code.png';
        Storage::put($qrCodePath, $qrCodeResult->getString());

        // Ambil logo dalam base64
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/' . $settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }
        // Generate PDF
        $pdf = PDF::loadView('laporan.pdf', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'rekapAbsensi' => $rekapAbsensi,
            'totalPertemuan' => $totalPertemuan,
            'settings' => $settings,
            'logoBase64' => $logoBase64,
            'qrCodePath' => Storage::url($qrCodePath),
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-' . $jadwal->mataKuliah->name . '-' . now()->format('YmdHis') . '.pdf';
        return $pdf->stream($filename);

    }


}
