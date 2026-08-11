<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:laporan-list', ['only' => ['index']]);
        $this->middleware('permission:laporan-export', ['only' => ['generatePDF']]);
    }

    public function index()
    {
        activity_log('lihat_laporan', 'Admin mengakses halaman laporan');

        // Ambil daftar mata kuliah dari jadwal
        $mataKuliah = Jadwal::with('kurikulum.mataKuliah')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.laporan.index', [
            'mataKuliah' => $mataKuliah,
        ]);
    }

    public function generatePDF(Request $request)
    {
        // Validasi input
        $this->validate($request, [
            'jadwal_id' => 'required|exists:jadwal,id',
        ]);

        // Ambil jadwal dengan relasi
        $jadwal = Jadwal::with([
            'kurikulum.mataKuliah',
            'pertemuan' => fn ($query) => $query
                ->with('absensi.mahasiswa')
                ->orderBy('tanggal_pertemuan')
                ->orderBy('jam_mulai'),
        ])->findOrFail($request->jadwal_id);

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
        return match (strtolower((string) $status)) {
            'hadir' => 'H',
            'izin' => 'I',
            'sakit' => 'S',
            'tidak hadir', 'alpha', 'alpa', 'alfa' => 'A',
            default => '-',
        };
    }

    public function generatePDFDocument($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan)
    {
        $settings = Setting::first();
        $websiteUrl = $settings->website_url ?? 'https://example.com';

        // Generate QR code
        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($websiteUrl);
        $qrTempDir = storage_path('app/temp');
        if (! file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir.'/qr_'.md5($websiteUrl).'.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);

        // Ambil logo dalam base64
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }
        // Generate PDF
        $pdf = PDF::loadView('admin.laporan.pdf', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'rekapAbsensi' => $rekapAbsensi,
            'totalPertemuan' => $totalPertemuan,
            'settings' => $settings,
            'logoBase64' => $logoBase64,
            'qrFilePath' => $qrFilePath,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-'.$jadwal->kurikulum->mataKuliah->nama.'-'.now()->format('YmdHis').'.pdf';

        return $pdf->stream($filename);

    }
}
