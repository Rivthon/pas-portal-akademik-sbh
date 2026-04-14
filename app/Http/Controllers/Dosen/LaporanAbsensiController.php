<?php

namespace App\Http\Controllers\Dosen;

use PDF;
use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\JadwalPraktik;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class LaporanAbsensiController extends Controller
{
     public function index()
    {
         // Ambil dosen yang sedang login dari guard 'dosen'
        $dosen = auth('dosen')->user();

        // Pastikan ada dosen yang login
        if (!$dosen) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai dosen!');
        }
         $activeTA = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (!$activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }
        // Ambil jadwal kuliah berdasarkan kurikulum yang diajar oleh dosen
       $dosen = auth('dosen')->user();
        $jadwalList = Jadwal::whereHas('kurikulum', function ($query) use ($activeTA, $dosen) {
            $query->where('ta_id', $activeTA->ta_id)
              ->where('jurusan_id', $dosen->jurusan_id);
        })
        ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->dosen_id);
        })
        ->with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen'])
        ->get()
        ->groupBy(function ($jadwal) {
            return $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak Ada Semester';
        })
        ->map(function ($jadwalPerSemester) {
            return $jadwalPerSemester->map(function ($jadwal) {
            return [
                'jadwal_id' => $jadwal->id,
                'hari' => $jadwal->hari ?? 'Tidak ada data',
                'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                'semester' => $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak Ada Semester',
                'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->matakuliah_id ?? null,
                'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                return [
                    'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                    'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                    'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                    'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                ];
                             })->filter(function ($dosen) {
                            return $dosen['jenis_dosen'] === 'teori'; // Hanya ambil dosen praktik

                        })->unique('id')->values(),
            ];
            });
        });

        $jadwalListPraktik = JadwalPraktik::whereHas('kurikulum', function ($query) use ($activeTA, $dosen) {
            $query->where('ta_id', $activeTA->ta_id)
              ->where('jurusan_id', $dosen->jurusan_id);
        })
        ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->dosen_id)
              ->where('jenis_dosen', 'praktik'); // Periksa jenis_dosen praktik
        })
        ->with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen'])
        ->get()
        ->groupBy(function ($jadwal) {
            return $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak Ada Semester';
        })
        ->map(function ($jadwalPerSemester) {
            return $jadwalPerSemester->map(function ($jadwal) {
            return [
                'jadwal_praktik_id' => $jadwal->id,
                'hari' => $jadwal->hari ?? 'Tidak ada data',
                'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->matakuliah_id ?? null,
                'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                'semester_matkul' => $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak ada data',
                'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                return [
                    'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                    'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                    'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                    'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                ];
                })->filter(function ($dosen) {
                return $dosen['jenis_dosen'] === 'praktik'; // Hanya ambil dosen praktik
                })->unique('id')->values(),
            ];
            });
        });

        return view('pages-dosen.absensi.cetak', compact('jadwalList','activeTA','jadwalListPraktik'));
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
            'kurikulum.dosenToMatakuliah.dosen', // Tambahkan ini
            'pertemuan.absensi'
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
            $dosenMatakuliah = DB::table('dosen_mata_kuliah')
            ->join('dosen', 'dosen_mata_kuliah.dosen_id', '=', 'dosen.dosen_id')
            ->join('kurikulum', 'dosen_mata_kuliah.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->where('dosen_mata_kuliah.jenis_dosen', 'teori')
            ->where('dosen_mata_kuliah.jenis_kelas', $jadwal->jenis_kelas)
            ->where('kurikulum.kurikulum_id', $jadwal->kurikulum->kurikulum_id)
            ->select('dosen.nama')
            ->get();
        // Generate QR code
        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($websiteUrl);
        $qrTempDir = storage_path('app/temp');
        if (!file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir . '/qr_' . md5($websiteUrl) . '.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);

        // Ambil logo dalam base64
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/' . $settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }
        // Generate PDF
        $pdf = PDF::loadView('pages-dosen.absensi.pdf', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'rekapAbsensi' => $rekapAbsensi,
            'totalPertemuan' => $totalPertemuan,
            'settings' => $settings,
            'logoBase64' => $logoBase64,
            'qrFilePath' => $qrFilePath,
            'dosenMatakuliah' => $dosenMatakuliah,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-' . $jadwal->kurikulum->mataKuliah->nama  . '-' . now()->format('YmdHis') . '.pdf';
        return $pdf->stream($filename);

    }
    public function generatePertemuan(Request $request)
    {
        // Validasi input
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
        ]);

        // Ambil jadwal dengan relasi ke pertemuan dan absensi
        $jadwal = Jadwal::with(['kurikulum.mataKuliah', 'pertemuan.absensi'])
                        ->findOrFail($request->jadwal_id);

        // Load view dengan data jadwal
        $pdf = Pdf::loadView('pages-dosen.absensi.laporan-pdf', compact('jadwal'))
                ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Absensi-Dosen' . $jadwal->kurikulum->mataKuliah->nama . '.pdf');
    }
     public function generatePraktikPDF(Request $request)
    {
        // Validasi input
        $this->validate($request, [
            'jadwal_praktik_id' => 'required|exists:jadwal_praktik,id',
        ]);
        $jadwal = JadwalPraktik::with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen', // Tambahkan ini
            'pertemuan.absensi'
        ])->findOrFail($request->jadwal_praktik_id);

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
        return $this->generatePraktikPDFDocument($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan);
    }
     public function generatePraktikPDFDocument($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan)
    {
        $settings = Setting::first();
        $websiteUrl = $settings->website_url ?? 'https://example.com';
         $dosenMatakuliah = DB::table('dosen_mata_kuliah')
            ->join('dosen', 'dosen_mata_kuliah.dosen_id', '=', 'dosen.dosen_id')
            ->join('kurikulum', 'dosen_mata_kuliah.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->where('dosen_mata_kuliah.jenis_dosen', 'praktik')
            ->where('dosen_mata_kuliah.jenis_kelas', $jadwal->jenis_kelas)
            ->where('kurikulum.kurikulum_id', $jadwal->kurikulum->kurikulum_id)
            ->select('dosen.nama')
            ->get();
        // Generate QR code
        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($websiteUrl);
        $qrTempDir = storage_path('app/temp');
        if (!file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir . '/qr_' . md5($websiteUrl) . '.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);

        // Ambil logo dalam base64
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/' . $settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }
        // Generate PDF
        $pdf = PDF::loadView('pages-dosen.absensi.pdf-praktik', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'rekapAbsensi' => $rekapAbsensi,
            'totalPertemuan' => $totalPertemuan,
            'settings' => $settings,
            'logoBase64' => $logoBase64,
            'qrFilePath' => $qrFilePath,
            'dosenMatakuliah' => $dosenMatakuliah,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-' . $jadwal->kurikulum->mataKuliah->nama  . '-' . now()->format('YmdHis') . '.pdf';
        return $pdf->stream($filename);

    }
    public function generatePraktikPertemuan(Request $request)
    {
        // Validasi input
        $request->validate([
            'jadwal_praktik_id' => 'required|exists:jadwal_praktik,id',
        ]);

        // Ambil jadwal dengan relasi ke pertemuan dan absensi
        $jadwal = JadwalPraktik::with(['kurikulum.mataKuliah', 'pertemuan.absensi'])
                        ->findOrFail($request->jadwal_praktik_id);

        // Load view dengan data jadwal
        $pdf = Pdf::loadView('pages-dosen.absensi.laporan-praktik-pdf', compact('jadwal'))
                ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Absensi-Dosen' . $jadwal->kurikulum->mataKuliah->nama . '.pdf');
    }
}
