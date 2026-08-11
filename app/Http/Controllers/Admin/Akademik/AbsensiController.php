<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
// use Illuminate\Support\Carbon;
use App\Models\Pertemuan;
use App\Models\ProgramStudi;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('permission:absensi-list', ['only' => ['index', 'getFilteredAbsensi', 'detail', 'riwayat', 'show']]);
        $this->middleware('permission:absensi-edit', ['only' => ['openAbsensi', 'closeAbsensi', 'tutupSesiAbsensi', 'bukaSesiAbsensi', 'updateStatus']]);
    }

    // Menampilkan halaman utama dengan filter
    public function index(Request $request): View
    {
        $tahunAkademik = TahunAkademik::orderBy('ta_id', 'desc')->get();
        $programStudi = ProgramStudi::all();
        $dosen = Dosen::orderBy('nama', 'asc')->get();

        return view('admin.akademik.absensi.index', compact('tahunAkademik', 'programStudi', 'dosen'));
    }

    public function getFilteredAbsensi(Request $request)
    {
        // 1. Validasi Input Dasar (Agar aman dari serangan SQL / error format)
        $request->validate([
            'ta_id' => 'nullable',
            'prodi_id' => 'nullable',
            'semester' => 'nullable',
            'dosen_id' => 'nullable',
        ]);

        try {
            // 2. Mulai Query Utama dengan Eager Loading
            $query = Jadwal::with([
                'kurikulum.mataKuliah',
                'ruangan',
                'pertemuan.absensi',
                'kurikulum.dosenToMatakuliah.dosen',
            ])->withCount('pertemuan');

            // 3. Terapkan Filter menggunakan ->when() agar kode lebih bersih
            $query->when($request->filled('ta_id'), function ($q) use ($request) {
                $q->where('ta_id', $request->ta_id);
            })
                ->when($request->filled('prodi_id'), function ($q) use ($request) {
                    $q->where('jurusan_id', $request->prodi_id);
                })
                ->when($request->filled('semester'), function ($q) use ($request) {
                    $q->whereHas('kurikulum.mataKuliah', function ($sub) use ($request) {
                        $sub->where('smt', $request->semester);
                    });
                })
                ->when($request->filled('dosen_id'), function ($q) use ($request) {
                    $q->whereHas('kurikulum.dosenToMatakuliah', function ($sub) use ($request) {
                        $sub->where('dosen_id', $request->dosen_id);
                    });
                });

            // 4. Kloning Query untuk Statistik (SANGAT PENTING untuk efisiensi)
            // Kita ambil ID dan status saja agar memori server tidak penuh load semua relasi
            $statQuery = clone $query;
            // Ambil data tanpa relasi (karena relasi with() bikin lambat kalau cuma buat dihitung)
            $statQuery->setEagerLoads([]);
            $jadwalsStat = $statQuery->get(['id', 'status_absensi', 'pertemuan_count']);

            // 5. Paginasi Data untuk Tabel (15 data per halaman)
            $jadwal = $query->paginate(15);

            // 6. Hitung Statistik Langsung dari memory collection yang ringan
            $statistik = [
                'total' => $jadwalsStat->count(),
                'aktif' => $jadwalsStat->where('status_absensi', 1)->count(),
                'selesai' => $jadwalsStat->filter(function ($j) {
                    return $j->status_absensi == 0 && $j->pertemuan_count >= 14;
                })->count(),
                'belum' => $jadwalsStat->filter(function ($j) {
                    return $j->status_absensi == 0 && $j->pertemuan_count < 14;
                })->count(),
            ];

            // 7. Render View HTML (Pastikan path file partial ini benar)
            $html = view('admin.akademik.absensi._partial_table', compact('jadwal'))->render();

            return response()->json([
                'html' => $html,
                'statistik' => $statistik,
            ]);

        } catch (\Throwable $e) {
            // 8. Tangkap Error dan catat Line berapa yang error agar mudah dilacak
            \Log::error('ABSENSI ERROR: '.$e->getMessage().' pada baris '.$e->getLine());

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat memfilter data.',
                // Tampilkan pesan error asli JIKA aplikasi masih tahap development (local)
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function cetakRekapMahasiswa(Request $request)
    {
        $request->validate(['jadwal_id' => 'required|exists:jadwal,id']);

        $jadwal = Jadwal::with([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'kurikulum.dosenToMatakuliah.dosen',
            'pertemuan' => fn ($query) => $query
                ->with('absensi')
                ->orderBy('tanggal_pertemuan')
                ->orderBy('jam_mulai'),
        ])->findOrFail($request->jadwal_id);

        $totalPertemuan = $jadwal->pertemuan->count();

        $rekapAbsensi = $jadwal->pertemuan->map(function ($pertemuan) {
            return [
                'topik' => $pertemuan->topik,
                'tanggal' => $pertemuan->tanggal_pertemuan,
                'absensi' => $pertemuan->absensi->groupBy('mahasiswa_id')->mapWithKeys(function ($absensiRecords, $mahasiswaId) {
                    $status = $absensiRecords->first()->status ?? '-';

                    return [$mahasiswaId => $this->mapAbsensiStatus($status)];
                }),
            ];
        });

        $mahasiswa = $jadwal->pertemuan->flatMap(function ($pertemuan) {
            return $pertemuan->absensi->map(function ($absensi) {
                return $absensi->mahasiswa;
            });
        })->unique('mahasiswa_id')->values();

        if ($rekapAbsensi->isEmpty() || $mahasiswa->isEmpty()) {
            return back()->with('error', 'Data absensi tidak tersedia untuk jadwal ini.');
        }

        activity_log('admin_cetak_absensi_teori', 'Admin mencetak laporan absensi teori jadwal ID: '.$request->jadwal_id);

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
        $dosenMatakuliah = DB::table('dosen_mata_kuliah')
            ->join('dosen', 'dosen_mata_kuliah.dosen_id', '=', 'dosen.dosen_id')
            ->join('kurikulum', 'dosen_mata_kuliah.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->where('dosen_mata_kuliah.jenis_dosen', 'teori')
            ->where('dosen_mata_kuliah.jenis_kelas', $jadwal->jenis_kelas)
            ->where('kurikulum.kurikulum_id', $jadwal->kurikulum->kurikulum_id)
            ->select('dosen.nama')
            ->get();

        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($websiteUrl);
        $qrTempDir = storage_path('app/temp');
        if (! file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir.'/qr_'.md5($websiteUrl).'.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);

        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = PDF::loadView('dosen.absensi.pdf', [
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'rekapAbsensi' => $rekapAbsensi,
            'totalPertemuan' => $totalPertemuan,
            'settings' => $settings,
            'logoBase64' => $logoBase64,
            'qrFilePath' => $qrFilePath,
            'dosenMatakuliah' => $dosenMatakuliah,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-'.$jadwal->kurikulum->mataKuliah->nama.'-'.now()->format('YmdHis').'.pdf';

        return $pdf->stream($filename);
    }

    public function cetakBAPDosen(Request $request)
    {
        $request->validate(['jadwal_id' => 'required|exists:jadwal,id']);

        $jadwal = Jadwal::with(['kurikulum.mataKuliah', 'pertemuan.absensi'])
            ->findOrFail($request->jadwal_id);

        $pdf = PDF::loadView('dosen.absensi.laporan-pdf', compact('jadwal'))
            ->setPaper('a4', 'landscape');

        activity_log('admin_cetak_bap_teori', 'Admin mencetak laporan BAP teori jadwal ID: '.$request->jadwal_id);

        return $pdf->stream('Laporan-BAP-Absensi-'.$jadwal->kurikulum->mataKuliah->nama.'.pdf');
    }

    public function cetakJurnalMengajar(Request $request)
    {
        $request->validate(['jadwal_id' => 'required|integer|exists:jadwal,id']);

        $jadwal = Jadwal::with([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'kurikulum.dosenToMatakuliah.dosen',
            'tahunAjaran',
            'ruangan',
            'pertemuan' => fn ($query) => $query
                ->whereDate('tanggal_pertemuan', '<=', now()->toDateString())
                ->with(['dosen', 'absensi.mahasiswa'])
                ->orderBy('tanggal_pertemuan')
                ->orderBy('jam_mulai')
                ->limit(14),
        ])->findOrFail($request->integer('jadwal_id'));

        if ($jadwal->pertemuan->isEmpty()) {
            return back()->with('error', 'Belum ada pertemuan yang dapat dimasukkan ke Jurnal Mengajar.');
        }

        $rekapAbsensi = $jadwal->pertemuan->map(fn ($pertemuan) => [
            'topik' => $pertemuan->topik,
            'tanggal' => $pertemuan->tanggal_pertemuan,
            'absensi' => $pertemuan->absensi->mapWithKeys(fn ($absensi) => [
                $absensi->mahasiswa_id => $this->mapAbsensiStatus($absensi->status),
            ]),
        ]);
        $mahasiswa = $jadwal->pertemuan
            ->flatMap(fn ($pertemuan) => $pertemuan->absensi->pluck('mahasiswa'))
            ->filter()
            ->unique('mahasiswa_id')
            ->sortBy('nama')
            ->values();

        if ($mahasiswa->isEmpty()) {
            return back()->with('error', 'Data absensi mahasiswa belum tersedia untuk Jurnal Mengajar ini.');
        }

        $jenisKelas = strtolower((string) $jadwal->jenis_kelas);
        $dosenMatakuliah = $jadwal->kurikulum->dosenToMatakuliah
            ->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === 'teori'
                && strtolower((string) $assignment->jenis_kelas) === $jenisKelas)
            ->pluck('dosen')
            ->filter()
            ->concat($jadwal->pertemuan->pluck('dosen')->filter())
            ->unique('dosen_id')
            ->values();
        $totalPertemuan = $jadwal->pertemuan->count();
        $kaprodiSignature = $this->signatureData($jadwal->kurikulum?->programStudi?->ttd);

        $pdf = PDF::loadView('admin.akademik.absensi.jurnal-mengajar-pdf', compact(
            'jadwal',
            'rekapAbsensi',
            'mahasiswa',
            'dosenMatakuliah',
            'totalPertemuan',
            'kaprodiSignature'
        ))->setPaper('a4', 'landscape');

        $namaMatakuliah = str($jadwal->kurikulum?->mataKuliah?->nama ?? 'mata-kuliah')->slug();
        activity_log(
            'admin_download_jurnal_mengajar',
            'Admin/BAAK mengunduh Jurnal Mengajar jadwal ID: '.$jadwal->id
        );

        return $pdf->download('Jurnal-Mengajar-'.$namaMatakuliah.'.pdf');
    }

    private function signatureData(?string $relativePath): ?string
    {
        if (! filled($relativePath)) {
            return null;
        }

        $path = storage_path('app/public/'.$relativePath);
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    public function detail($jadwalId, $pertemuanId, Request $request): View
    {
        // Set the locale for Carbon to Indonesian
        Carbon::setLocale('id');

        // Retrieve the schedule and the selected meeting
        $jadwal = Jadwal::with('kurikulum.mataKuliah')->findOrFail($jadwalId);

        // Retrieve the specific pertemuan (meeting)
        $pertemuan = Pertemuan::where('jadwal_id', $jadwalId)->findOrFail($pertemuanId);

        // Parse the pertemuan date
        $tanggal = Carbon::parse($pertemuan->tanggal_pertemuan);

        // IMPROVEMENT:
        // 1. Hapus eager load 'jadwal' untuk menghemat memori (karena sudah ada $jadwal dari query di atas).
        // 2. Tambahkan sorting (pengurutan) berdasarkan nama mahasiswa agar tabel rapi sesuai abjad.
        $absensi = Absensi::where('pertemuan_id', $pertemuanId)
            ->with('mahasiswa')
            ->get()
            ->sortBy(function ($absen) {
                return $absen->mahasiswa->nama ?? 'ZZZ'; // ZZZ agar yang tidak punya nama taruh di paling bawah
            })
            ->values(); // Reset urutan index array setelah di-sort (agar $index di Blade mulai dari 0 berurutan)

        return view('admin.akademik.absensi.detail', compact('absensi', 'jadwal', 'pertemuan', 'tanggal'));
    }

    public function updateMassal(Request $request)
    {
        // 1. Validasi input array
        $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,pertemuan_id',
            'status' => 'required|array',
            'status.*' => 'required|in:hadir,izin,sakit,tidak hadir',
        ]);

        try {
            // 2. Looping array dan update status masing-masing mahasiswa
            if ($request->has('status')) {
                foreach ($request->status as $absensi_id => $status_kehadiran) {
                    Absensi::where('absensi_id', $absensi_id)->update([
                        'status' => $status_kehadiran,
                    ]);
                }
            }

            // 3. Redirect kembali dengan pesan sukses
            return redirect()->back()->with('success', 'Seluruh data absensi berhasil diperbarui.');

        } catch (\Exception $e) {
            \Log::error('UPDATE MASSAL ERROR: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data absensi.');
        }
    }

    public function openAbsensi($jadwalId)
    {
        $jadwal = Jadwal::findOrFail($jadwalId);
        $jadwal->status_absensi = true; // Mengubah status menjadi terbuka
        $jadwal->save();

        return redirect()->route('admin.absensi.index')
            ->with('success', 'Absensi berhasil dibuka');
    }

    // Method untuk menutup absensi
    public function closeAbsensi($jadwalId)
    {
        $jadwal = Jadwal::findOrFail($jadwalId);
        $jadwal->status_absensi = false; // Mengubah status menjadi tertutup
        $jadwal->save();

        return redirect()->route('admin.absensi.index')
            ->with('success', 'Absensi berhasil ditutup');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function tutupSesiAbsensi($jadwalId)
    {
        // Temukan jadwal berdasarkan ID
        $jadwal = Jadwal::findOrFail($jadwalId);

        // Set status_absensi menjadi 0 (tutup)
        $jadwal->status_absensi = 0;
        $jadwal->save();

        return redirect()->route('admin.absensi.show', $jadwalId)
            ->with('success', 'Sesi absensi telah ditutup.');
    }

    // Fungsi untuk membuka sesi absensi
    public function bukaSesiAbsensi($jadwalId)
    {
        // Temukan jadwal berdasarkan ID
        $jadwal = Jadwal::findOrFail($jadwalId);

        // Set status_absensi menjadi 1 (aktif)
        $jadwal->status_absensi = 1;
        $jadwal->save();

        return redirect()->route('admin.absensi.show', $jadwalId)
            ->with('success', 'Sesi absensi telah dibuka.');
    }

    public function updateStatus($absensiId, Request $request)
    {
        // Validasi input status
        $request->validate([
            'status' => 'required|in:hadir,tidak hadir,sakit',
        ]);

        // Temukan absensi berdasarkan ID
        $absensi = Absensi::findOrFail($absensiId);

        // Update status kehadiran mahasiswa
        $absensi->status = $request->status;
        $absensi->save();

        // Redirect ke halaman detail absensi
        return redirect()->route('admin.absensi.detail', [
            'jadwal_id' => $absensi->jadwal_id,
            'pertemuan_id' => $absensi->pertemuan_id,
        ])->with('success', 'Status kehadiran berhasil diperbarui.');
    }

    public function riwayat(Request $request)
    {
        Carbon::setLocale('id');

        $tanggal = $request->tanggal ?? null;
        $jadwal = null;
        $pertemuan = null;
        $absensi = collect();

        if ($tanggal) {
            $request->validate([
                'tanggal' => 'required|date',
            ]);

            // Cari pertemuan berdasarkan tanggal
            $pertemuan = Pertemuan::where('tanggal_pertemuan', $tanggal)
                ->with('jadwal.kurikulum.mataKuliah')
                ->first();

            if ($pertemuan) {
                $jadwal = $pertemuan->jadwal;
                $absensi = Absensi::where('pertemuan_id', $pertemuan->pertemuan_id)
                    ->with('mahasiswa')
                    ->get()
                    ->sortBy(fn ($a) => $a->mahasiswa->nama ?? 'ZZZ')
                    ->values();
            }
        }

        return view('admin.akademik.absensi.detail', compact(
            'jadwal', 'pertemuan', 'absensi', 'tanggal'
        ));
    }

    public function show($jadwal_id)
    {
        Carbon::setLocale('id');

        $jadwal = Jadwal::with('kurikulum.mataKuliah')->findOrFail($jadwal_id);

        // Ambil pertemuan terbaru untuk jadwal ini
        $pertemuan = Pertemuan::where('jadwal_id', $jadwal_id)
            ->orderBy('tanggal_pertemuan', 'desc')
            ->first();

        $tanggal = $pertemuan ? Carbon::parse($pertemuan->tanggal_pertemuan) : null;

        // Ambil absensi berdasarkan pertemuan terbaru
        $absensi = collect();
        if ($pertemuan) {
            $absensi = Absensi::where('pertemuan_id', $pertemuan->pertemuan_id)
                ->with('mahasiswa')
                ->get()
                ->sortBy(fn ($a) => $a->mahasiswa->nama ?? 'ZZZ')
                ->values();
        }

        return view('admin.akademik.absensi.detail', compact(
            'jadwal',
            'absensi',
            'pertemuan',
            'tanggal'
        ));
    }
}
