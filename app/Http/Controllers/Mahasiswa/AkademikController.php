<?php

namespace App\Http\Controllers\Mahasiswa;

use PDF;
use App\Models\Krs;
use App\Models\Setting;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class AkademikController extends Controller
{
        public function index(Request $request)

        {
            $semester = Auth::guard('mahasiswa')->user()->semester;
            $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
            $search = $request->input('search');
            $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

            // Query awal dengan eager loading
            $krs = Kurikulum::with(['programStudi', 'mataKuliah'])
                    ->where('ta_id', $activeTA->ta_id)
                    ->whereHas('mataKuliah', function ($query) use ($semester) {
                        $query->where('smt', $semester);
                    })
                    ->whereHas('programStudi', function ($query) {
                    $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id; // Ambil jurusan_id dari mahasiswa
                    $query->where('jurusan_id', $jurusanId);
                    })// Filter semester melalui relasi mataKuliah
                    ->get();



            // Jika bukan AJAX, kirim ke view utama
            return view('students.krs.index', compact('krs'));
        }



       public function nyimpenKrs(Request $request)
        {
            // Ambil ID mahasiswa yang sedang login
            $mahasiswa = Auth::guard('mahasiswa')->user();
            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa tidak ditemukan.',
                ], 401);
            }

            // Ambil Tahun Akademik Aktif
            $ta = TahunAkademik::where('status_ta', 1)->first();
            if (!$ta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun Akademik Aktif tidak ditemukan.',
                ], 400);
            }

            // Validasi input data
            $validated = $request->validate([
                'krs' => 'required|array|min:1',       // Harus array dan minimal ada 1 item
                'krs.*' => 'integer|exists:kurikulum,kurikulum_id', // Setiap item harus integer dan ada di tabel 'kurikulums'
            ]);

            try {
                foreach ($validated['krs'] as $kurikulumId) {
                    // Periksa apakah data KRS sudah ada
                    $existingKrs = Krs::where('kurikulum_id', $kurikulumId)
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                        ->where('ta_id', $ta->ta_id)
                        ->first();

                    if (!$existingKrs) {
                        // Jika tidak ada, buat entri baru
                        Krs::create([
                            'kurikulum_id' => $kurikulumId,
                            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                            'ta_id' => $ta->ta_id,
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'KRS berhasil disimpan.',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan KRS: ' . $e->getMessage(),
                ], 500);
            }
        }


    public function tampilkanKrs()
    {
         $mahasiswa = Auth::guard('mahasiswa')->user();
            if (!$mahasiswa) {
                return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
            }
           $mahasiswaId = $mahasiswa->mahasiswa_id;
           $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

        try {
               $krs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            // Redirect ke view baru dengan data KRS
            return view('students.krs.status-krs', compact('krs'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: ' . $e->getMessage());
        }
    }

    public function hapusKrs($id)
    {
        $krs = Krs::findOrFail($id); // Cari data berdasarkan ID
        $krs->delete(); // Hapus data
        Alert::success('Berhasil', 'KRS berhasil dihapus');
        return redirect()->back();
    }

    public function cetakKapro()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user(); // Mendapatkan data user yang login
        $mahasiswaId = $mahasiswa->mahasiswa_id; // ID Mahasiswa
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama','semester']);
        $settings = Setting::first(); // atau sesuai struktur tabel kamu
 // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        $headerKrs = null;
            if ($mahasiswa && $mahasiswa->programStudi->header_kapro) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->header_kapro);
                if (file_exists($logoPath)) {
                    $headerKrs = base64_encode(file_get_contents($logoPath));
                }
            }
            $ttd = null;
            if ($mahasiswa && $mahasiswa->programStudi->ttd) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->ttd);
                if (file_exists($logoPath)) {
                    $ttd = base64_encode(file_get_contents($logoPath));
                }
            }
            $logo = null;
            $setting = Setting::first(); // atau ->where('id', 1)->first();
            if ($setting && $setting->logo) {
                $logoPath = public_path('storage/' . $setting->logo);
                if (file_exists($logoPath)) {
                    $logo = base64_encode(file_get_contents($logoPath));
                }
            }

        try {
            $krs = Krs::with(['kurikulum.mataKuliah'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                $query->where('smt', $mahasiswa->semester);
            })
            ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('students.krs.cetak-pdf-kapro', compact('krs', 'mahasiswa', 'taId', 'headerKrs', 'ttd', 'ta','logo','settings'))
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->setPaper('a4', 'portrait');

            // Stream file PDF
            return $pdf->stream('KRS-Mahasiswa-Kaprodi.pdf');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal mencetak KRS: ' . $e->getMessage());
        }
    }
    public function cetakDospem()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user(); // Mendapatkan data user yang login
        $mahasiswaId = $mahasiswa->mahasiswa_id; // ID Mahasiswa
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama','semester']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
                $settings = Setting::first(); // atau sesuai struktur tabel kamu

        $headerKrs = null;
            if ($mahasiswa && $mahasiswa->programStudi->header_dospem) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->header_dospem);
                if (file_exists($logoPath)) {
                    $headerKrs = base64_encode(file_get_contents($logoPath));
                }
            }
            $ttd = null;
            if ($mahasiswa && $mahasiswa->programStudi->ttd) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->ttd);
                if (file_exists($logoPath)) {
                    $ttd = base64_encode(file_get_contents($logoPath));
                }
            }
               $logo = null;
            $setting = Setting::first(); // atau ->where('id', 1)->first();
            if ($setting && $setting->logo) {
                $logoPath = public_path('storage/' . $setting->logo);
                if (file_exists($logoPath)) {
                    $logo = base64_encode(file_get_contents($logoPath));
                }
            }

        try {
             $krs = Krs::with(['kurikulum.mataKuliah'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                $query->where('smt', $mahasiswa->semester);
            })
            ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('students.krs.cetak-pdf-dospem', compact('krs', 'mahasiswa', 'taId','headerKrs','ttd','ta','logo','settings'))
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                    ->setPaper('a4', 'portrait');

            // Download file PDF
            return $pdf->stream('KRS-Mahasiswa-Dospem.pdf');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal mencetak KRS: ' . $e->getMessage());
        }
    }
    public function cetakBaak()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user(); // Mendapatkan data user yang login
        $mahasiswaId = $mahasiswa->mahasiswa_id; // ID Mahasiswa
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama','semester']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        $settings = Setting::first(); // atau sesuai struktur tabel kamu

        $headerKrs = null;
            if ($mahasiswa && $mahasiswa->programStudi->header_baak) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->header_baak);
                if (file_exists($logoPath)) {
                    $headerKrs = base64_encode(file_get_contents($logoPath));
                }
            }
            $ttd = null;
            if ($mahasiswa && $mahasiswa->programStudi->ttd) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->ttd);
                if (file_exists($logoPath)) {
                    $ttd = base64_encode(file_get_contents($logoPath));
                }
            }
         $logo = null;
            $setting = Setting::first(); // atau ->where('id', 1)->first();
            if ($setting && $setting->logo) {
                $logoPath = public_path('storage/' . $setting->logo);
                if (file_exists($logoPath)) {
                    $logo = base64_encode(file_get_contents($logoPath));
                }
            }

        try {
             $krs = Krs::with(['kurikulum.mataKuliah'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                $query->where('smt', $mahasiswa->semester);
            })
            ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('students.krs.cetak-pdf-baak', compact('krs', 'mahasiswa', 'taId','headerKrs','ttd','ta','logo','settings'))
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                    ->setPaper('a4', 'portrait');

            // Download file PDF
            return $pdf->stream('KRS-Mahasiswa-BAAK.pdf');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal mencetak KRS: ' . $e->getMessage());
        }
    }

    public function tampilanKartuHasil()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']);
        if (!$ta) {
            return redirect()->back()->with('error', 'Tahun Akademik tidak ditemukan.');
        }

        try {
            // Ambil KHS semester aktif
            $khs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get()
                ->filter(function ($item) {
                    return $item->kurikulum && $item->kurikulum->mataKuliah;
                });

            [$ipsTotalSks, $ipsTotalBobot] = $this->calculateTotal($khs);
            $ips = $ipsTotalSks > 0 ? $ipsTotalBobot / $ipsTotalSks : 0;

            // Ambil seluruh KHS untuk IPK
            $allKhs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->get()
                ->filter(function ($item) {
                    return $item->kurikulum && $item->kurikulum->mataKuliah && !is_null($item->khs);
                });

            [$ipkTotalSks, $ipkTotalBobot] = $this->calculateTotal($allKhs);
            $ipk = $ipkTotalSks > 0 ? $ipkTotalBobot / $ipkTotalSks : 0;

            return view('students.khs.index', compact('khs', 'mahasiswa', 'ta', 'ips', 'ipk'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data KHS: ' . $e->getMessage());
        }
    }

    /**
     * Hitung total SKS dan total Bobot dari data KRS
     */
    private function calculateTotal($khsCollection)
    {
        $totalSks = $khsCollection->sum(function ($item) {
            return $item->kurikulum->mataKuliah->sks ?? 0;
        });

        $totalBobot = $khsCollection->sum(function ($item) {
            $sks = $item->kurikulum->mataKuliah->sks ?? 0;
            $bobot = $this->calculateWeight($item->khs);
            return $sks * $bobot;
        });

        return [$totalSks, $totalBobot];
    }

    private function calculateWeight($grade)
    {
        return match ($grade) {
            'A'  => 4.00,
            'AB' => 3.75,
            'BA' => 3.50,
            'B'  => 3.00,
            'BC' => 2.75,
            'C'  => 2.00,
            'D'  => 1.00,
            'E'  => 0,
            default => 0,
        };
    }

    private function getPredikat($ipk)
    {
        return match (true) {
            $ipk >= 3.51 => 'Cumlaude',
            $ipk >= 3.00 => 'Sangat Memuaskan',
            $ipk >= 2.50 => 'Memuaskan',
            $ipk >= 2.00 => 'Cukup',
            default => 'Kurang',
        };
    }


   public function cetakKhs()
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);

        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/' . $settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $programStudi = strtolower($mahasiswa->jurusan_id ?? '');
        $headerColor = match ($programStudi) {
            '13211' => '#fffbea',
            '48201' => '#f3e8ff',
            '15401' => '#eaf6ff',
            default => '#f3e8ff',
        };
        $textColor = match ($programStudi) {
            '13211' => '#a68c00',
            '48201' => '#6b3fa0',
            '15401' => '#005a9e',
            default => '#6b3fa0',
        };

        try {
            // Ambil KHS Semester Ini
            $khs = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->where('krs.mahasiswa_id', $mahasiswaId)
                ->where('matakuliah.smt', $mahasiswa->semester)
                ->select('krs.*', 'matakuliah.nama as nama', 'matakuliah.sks')
                ->get();

            // Hitung IPS
            $totalSks = $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks);
            $totalSksAm = $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks * $this->calculateWeight($item->khs));
            $ips = $totalSks > 0 ? $totalSksAm / $totalSks : 0;

            // Ambil Semua KHS untuk Hitung IPK
            // Perhitungan IPK (Dari Semua Semester)
            $allKhs = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
            ->where('krs.mahasiswa_id', $mahasiswaId)
            ->where('matakuliah.smt', $mahasiswa->semester)
            ->select('krs.*', 'matakuliah.nama as nama', 'matakuliah.sks')
            ->get();

            // Filter data agar hanya yang memiliki nilai 'khs' yang tidak null
            $filteredAllKhs = $allKhs->filter(fn($item) => !is_null($item->khs));

            $totalSksAll = $filteredAllKhs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);
            $totalBobotAll = $filteredAllKhs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks * $this->calculateWeight($item->khs));

            $ipk = $totalSksAll > 0 ? $totalBobotAll / $totalSksAll : 0;
            // Tentukan predikat berdasarkan IPK
            $predikat = $this->getPredikat($ipk);

            // Generate PDF
            $pdf = PDF::loadView('students.khs.pdf', compact(
                'khs', 'mahasiswa', 'ta', 'logoBase64', 'headerColor', 'textColor', 'ips', 'ipk', 'predikat'
            ))->setPaper('a4', 'portrait');

            return $pdf->stream('khs-' . $mahasiswa->nama . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data KHS: ' . $e->getMessage());
        }
    }

 public function cetakTranskrip()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user(); // Data mahasiswa login
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        // Ambil Tahun Akademik Aktif
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (!$ta) {
            return redirect()->back()->with('error', 'Tahun Akademik tidak ditemukan.');
        }

        $taId = $ta->ta_id;

        try {
            // Ambil Data KRS beserta Mata Kuliah
            $khs = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
            ->where('krs.mahasiswa_id', $mahasiswaId)
            ->select('krs.*', 'matakuliah.nama as nama', 'matakuliah.sks')
            ->get();


            if ($khs->isEmpty()) {
                return redirect()->back()->with('error', 'Data KHS tidak ditemukan.');
            }

            // Perhitungan IPS
            $totalSks = $khs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);
            $totalBobot = $khs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks * $this->calculateWeight($item->khs));
            $ips = $totalSks > 0 ? $totalBobot / $totalSks : 0;

            // Perhitungan IPK (Dari Semua Semester)
            $allKhs = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
            ->where('krs.mahasiswa_id', $mahasiswaId)
            ->select('krs.*', 'matakuliah.nama as nama', 'matakuliah.sks')
            ->get();

            // Filter data agar hanya yang memiliki nilai 'khs' yang tidak null
            $filteredAllKhs = $allKhs->filter(fn($item) => !is_null($item->khs));
            $totalSksAll = $filteredAllKhs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);
            $totalBobotAll = $filteredAllKhs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks * $this->calculateWeight($item->khs));
            $ipk = $totalSksAll > 0 ? $totalBobotAll / $totalSksAll : 0;

            // Tentukan Predikat
            $predikat = $this->getPredikat($ipk);

            // Set warna default jika tidak ada
            $programStudi = strtolower($mahasiswa->jurusan_id ?? '');
            $headerColor = match ($programStudi) {
                '13211' => '#fffbea',
                '48201' => '#f3e8ff',
                '15401' => '#eaf6ff',
                default => '#f3e8ff',
            };
            $textColor = match ($programStudi) {
                '13211' => '#a68c00',
                '48201' => '#6b3fa0',
                '15401' => '#005a9e',
                default => '#6b3fa0',
            };

            // Generate PDF
            $pdf = PDF::loadView('students.pengajuan.cetak-transkrip', compact(
                'khs', 'mahasiswa', 'ta', 'ips', 'ipk', 'predikat', 'headerColor', 'textColor'
            ))->setPaper('F4', 'portrait');

            // dd($pdf); // Untuk cek isi $pdf

            return $pdf->stream('transkrip.pdf');
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTrace());
        }
    }
}
