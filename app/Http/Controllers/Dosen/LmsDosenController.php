<?php

namespace App\Http\Controllers\Dosen;

use App\Exports\GradebookExport;
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsMateri;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\LmsTugasSoal;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use App\Services\GradebookKhsSyncService;
use App\Services\LmsCalendarService;
use App\Support\StoredUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class LmsDosenController extends Controller
{
    public function index(LmsCalendarService $calendar)
    {
        $dosen = auth('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $jadwalList = Jadwal::with([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'kurikulum.dosenToMatakuliah.dosen',
            'pertemuan.dosen',
            'ruangan',
        ])
            ->withCount(['pertemuan', 'materi', 'tugas', 'quiz'])
            ->where('ta_id', $activeTA->ta_id)
            ->accessibleInLmsByDosen($dosen->dosen_id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $calendarEvents = $calendar->events(
            $jadwalList->pluck('id'),
            'dosen',
            'dosen',
            (int) $dosen->dosen_id
        );

        return view('dosen.lms.index', compact('jadwalList', 'activeTA', 'calendarEvents'));
    }

    public function kelola($jadwalId)
    {
        $jadwal = $this->jadwalMilikDosen($jadwalId);
        $jadwal->load(['kurikulum.mataKuliah', 'kurikulum.programStudi', 'ruangan']);

        $pertemuan = Pertemuan::with([
            'materi',
            'tugas.pengumpulan',
            'tugas.soal',
        ])
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('pertemuan_id', 'asc')
            ->get();

        return view('dosen.lms.kelola', compact(
            'jadwal',
            'pertemuan'
        ));
    }

    public function gradebook(Jadwal $jadwal)
    {
        $jadwal = $this->jadwalMilikDosen($jadwal->id);
        $jadwal->load(['kurikulum.mataKuliah', 'kurikulum.programStudi']);

        $tugasList = LmsTugas::where('jadwal_id', $jadwal->id)
            ->with(['pertemuan', 'pengumpulan'])
            ->orderBy('pertemuan_id')
            ->orderBy('deadline')
            ->get();
        $quizList = LmsQuiz::where('jadwal_id', $jadwal->id)
            ->with(['soal', 'attempts'])
            ->orderBy('deadline')
            ->get()
            ->each(function ($quiz) {
                $quiz->nilai_maksimal_gradebook = (float) $quiz->soal->sum('bobot');
            });

        $kelasJadwal = strtolower((string) $jadwal->jenis_kelas);
        $peserta = Krs::with('mahasiswa')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->whereHas('mahasiswa', function ($query) use ($kelasJadwal) {
                if ($kelasJadwal === 'karyawan') {
                    $query->whereRaw('LOWER(kelas) = ?', ['karyawan']);
                } else {
                    $query->where(function ($kelas) {
                        $kelas->whereNull('kelas')
                            ->orWhereRaw('LOWER(kelas) != ?', ['karyawan']);
                    });
                }
            })
            ->get()
            ->pluck('mahasiswa')
            ->filter()
            ->unique('mahasiswa_id')
            ->sortBy(fn ($mahasiswa) => $mahasiswa->nama ?? '')
            ->values();

        $pengumpulan = $tugasList->flatMap->pengumpulan
            ->keyBy(fn ($item) => $item->mahasiswa_id.'-'.$item->tugas_id);
        $attemptQuiz = $quizList->flatMap->attempts
            ->keyBy(fn ($item) => $item->mahasiswa_id.'-'.$item->quiz_id);
        $totalMaksimal = (float) $tugasList->sum('nilai_maksimal')
            + (float) $quizList->sum('nilai_maksimal_gradebook');

        $rekap = $peserta->map(function ($mahasiswa) use (
            $tugasList, $quizList, $pengumpulan, $attemptQuiz, $totalMaksimal
        ) {
            $nilaiDiperoleh = 0;
            $jumlahDinilai = 0;
            $nilaiPerTugas = [];
            $nilaiPerQuiz = [];

            foreach ($tugasList as $tugas) {
                $item = $pengumpulan->get($mahasiswa->mahasiswa_id.'-'.$tugas->tugas_id);
                $nilaiPerTugas[$tugas->tugas_id] = $item;

                if ($item && $item->nilai !== null) {
                    $nilaiDiperoleh += (float) $item->nilai;
                    $jumlahDinilai++;
                }
            }
            foreach ($quizList as $quiz) {
                $item = $attemptQuiz->get($mahasiswa->mahasiswa_id.'-'.$quiz->quiz_id);
                $nilaiPerQuiz[$quiz->quiz_id] = $item;
                if ($item && $item->status === 'graded' && $item->nilai_total !== null) {
                    $nilaiDiperoleh += (float) $item->nilai_total;
                    $jumlahDinilai++;
                }
            }

            return (object) [
                'mahasiswa' => $mahasiswa,
                'nilai_per_tugas' => $nilaiPerTugas,
                'nilai_per_quiz' => $nilaiPerQuiz,
                'nilai_diperoleh' => $nilaiDiperoleh,
                'jumlah_dinilai' => $jumlahDinilai,
                'persentase' => $totalMaksimal > 0
                    ? round(($nilaiDiperoleh / $totalMaksimal) * 100, 1)
                    : 0,
            ];
        });

        $rataRata = $rekap->isNotEmpty() ? round($rekap->avg('persentase'), 1) : 0;
        $totalSudahDinilai = $tugasList->sum(fn ($tugas) => $tugas->pengumpulan
            ->whereNotNull('nilai')->count())
            + $quizList->sum(fn ($quiz) => $quiz->attempts->where('status', 'graded')->count());

        return view('dosen.lms.gradebook', compact(
            'jadwal',
            'tugasList',
            'quizList',
            'rekap',
            'totalMaksimal',
            'rataRata',
            'totalSudahDinilai'
        ));
    }

    public function exportGradebookExcel(Jadwal $jadwal)
    {
        $data = $this->gradebook($jadwal)->getData();
        $nama = preg_replace('/[^A-Za-z0-9_-]/', '_',
            $data['jadwal']->kurikulum?->mataKuliah?->nama ?? 'mata_kuliah');

        return Excel::download(
            new GradebookExport($data),
            'gradebook_'.$nama.'.xlsx'
        );
    }

    public function exportGradebookPdf(Jadwal $jadwal)
    {
        $data = $this->gradebook($jadwal)->getData();
        $nama = preg_replace('/[^A-Za-z0-9_-]/', '_',
            $data['jadwal']->kurikulum?->mataKuliah?->nama ?? 'mata_kuliah');

        return Pdf::loadView('dosen.lms.gradebook-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('gradebook_'.$nama.'.pdf');
    }

    public function syncGradebook(Jadwal $jadwal, GradebookKhsSyncService $syncService)
    {
        $jadwal = $this->jadwalMilikDosen($jadwal->id);
        $result = $syncService->sync($jadwal);

        if ($result['synced'] === 0) {
            return back()->with('error', $result['reason']);
        }

        activity_log(
            'sinkron_gradebook_khs',
            'Dosen menyinkronkan Gradebook ke nilai KHS ('.$result['synced'].' mahasiswa)'
        );

        return back()->with(
            'success',
            'Gradebook berhasil disinkronkan ke komponen Tugas KHS untuk '
            .$result['synced'].' mahasiswa. Bobot tugas: '
            .number_format((float) $result['bobot_tugas'], 0).'%.'
        );
    }

    /**
     * FUNGSI TAMBAHAN: HELPER PRIVATE UNTUK PROSES UPLOAD FILE
     * Mengurangi penulisan kode berulang di fungsi Store dan Update
     */
    private function processMateriFile(Request $request)
    {
        if (! $request->hasFile('file')) {
            return ['path' => null, 'tipe' => null];
        }

        $uploadedFile = $request->file('file');
        $filename = $uploadedFile->getClientOriginalName();
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        switch ($extension) {
            case 'pdf':
                $tipe = 'pdf';
                break;
            case 'ppt':
            case 'pptx':
                $tipe = 'ppt';
                break;
            case 'doc':
            case 'docx':
                $tipe = 'doc';
                break;
            case 'xls':
            case 'xlsx':
                $tipe = 'excel';
                break;
            case 'zip':
            case 'rar':
                $tipe = 'arsip';
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
                $tipe = 'gambar';
                break;
            case 'mp4':
            case 'avi':
            case 'mov':
            case 'mkv':
                $tipe = 'video';
                break;
            default:
                $tipe = 'lainnya';
                break;
        }

        $baseName = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
        $baseName = Str::limit($baseName ?: 'materi', 100, '');
        $namaFile = now()->format('YmdHis').'_'.Str::random(8).'_'.$baseName.'.'.$extension;
        $path = $uploadedFile->storeAs('lms/materi', $namaFile, 'public');

        if (! $path) {
            throw new RuntimeException('File materi gagal disimpan ke storage.');
        }

        return [
            'path' => $path,
            'tipe' => $tipe,
        ];
    }

    public function storeMateri(Request $request)
    {
        $dosen = Auth::guard('dosen')->user();

        $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,pertemuan_id',
            'jadwal_id' => 'required|exists:jadwal,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|required_without:youtube_url|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png,mp4,avi,mov,mkv|max:51200',
            'youtube_url' => 'nullable|required_without:file|url|max:255',
        ], [
            'file.required_without' => 'Upload file atau isi link materi.',
            'file.mimes' => 'Format file materi tidak didukung.',
            'file.max' => 'Ukuran file materi maksimal 50 MB.',
            'youtube_url.required_without' => 'Upload file atau isi link materi.',
            'youtube_url.url' => 'Link materi harus berupa URL yang valid.',
        ]);

        $jadwal = $this->jadwalMilikDosen($request->jadwal_id);
        abort_unless(
            Pertemuan::where('pertemuan_id', $request->pertemuan_id)
                ->where('jadwal_id', $jadwal->id)->exists(),
            422,
            'Pertemuan tidak sesuai dengan jadwal mata kuliah.'
        );

        $path = null;

        try {
            $fileProcessed = $this->processMateriFile($request);
            $path = $fileProcessed['path'];
            $tipe = $request->filled('youtube_url')
                ? 'youtube'
                : ($fileProcessed['tipe'] ?: 'lainnya');

            LmsMateri::create([
                'pertemuan_id' => $request->pertemuan_id,
                'jadwal_id' => $request->jadwal_id,
                'dosen_id' => $dosen->dosen_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tipe' => $tipe,
                'file' => $path,
                'youtube_url' => $request->youtube_url,
                'status' => 1,
            ]);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            report($exception);

            return back()
                ->withInput($request->except('file'))
                ->with('error', 'Materi gagal diunggah. Periksa file dan ruang penyimpanan, lalu coba kembali.');
        }

        return redirect()->route('dosen.lms.kelola', $request->jadwal_id)
            ->with('success', 'Materi berhasil diunggah.');
    }

    /**
     * FUNGSI BARU: UPDATE MATERI (UNTUK FORM EDIT)
     */
    public function updateMateri(Request $request, $id)
    {
        $materi = LmsMateri::findOrFail($id);
        $this->pastikanMateriMilikDosen($materi);

        $request->validate([
            'judul' => 'required|max:255',
            'deskripsi' => 'nullable',
            'file' => 'nullable|max:51200',
            'youtube_url' => 'nullable',
        ]);

        $materi->judul = $request->judul;
        $materi->deskripsi = $request->deskripsi;
        $materi->youtube_url = $request->youtube_url;

        // Jika dosen mengunggah berkas baru
        if ($request->hasFile('file')) {
            // Hapus berkas lama di storage jika ada
            if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                Storage::disk('public')->delete($materi->file);
            }

            // Gunakan helper private untuk memproses berkas baru
            $fileProcessed = $this->processMateriFile($request);
            $materi->file = $fileProcessed['path'];
            $materi->tipe = $fileProcessed['tipe'];
        }
        // Jika tidak upload file baru, tapi ada perubahan/pengisian link YouTube
        elseif ($request->filled('youtube_url')) {
            $materi->tipe = 'youtube';
        }

        $materi->save();

        return redirect()->route('dosen.lms.kelola', $materi->jadwal_id)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function downloadMateri(LmsMateri $materi)
    {
        $this->pastikanMateriMilikDosen($materi);
        if (! $materi->file || ! Storage::disk('public')->exists($materi->file)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        $fullPath = storage_path('app/public/'.$materi->file);

        return response()->file($fullPath);
    }

    public function destroyMateri(LmsMateri $materi)
    {
        $this->pastikanMateriMilikDosen($materi);
        if ($materi->file && Storage::disk('public')->exists($materi->file)) {
            Storage::disk('public')->delete($materi->file);
        }

        $materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }

    public function previewMateri(LmsMateri $materi)
    {
        $this->pastikanMateriMilikDosen($materi);
        switch ($materi->tipe) {
            case 'youtube':
                return redirect($materi->youtube_url);
            case 'video':
            case 'pdf':
            case 'gambar':
                return redirect(Storage::url($materi->file));
            case 'ppt':
            case 'doc':
            case 'excel':
            case 'arsip':
            default:
                return Storage::disk('public')->download($materi->file, basename($materi->file));
        }
    }

    public function storeTugas(Request $request)
    {
        $dosen = Auth::guard('dosen')->user();
        $request->merge([
            'tipe' => $request->input('tipe') ?: 'file',
        ]);

        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'pertemuan_id' => 'required|exists:pertemuan,pertemuan_id',
            'judul' => 'required|max:255',
            'deskripsi' => 'nullable',
            'tipe' => ['required', Rule::in(['file', 'pilihan_ganda'])],
            'deadline' => 'required|date',
            'nilai_maksimal' => 'required|integer|min:1|max:1000',
            'lampiran' => 'nullable|file|max:51200',
        ]);

        $jadwal = $this->jadwalMilikDosen($request->jadwal_id);
        abort_unless(
            Pertemuan::where('pertemuan_id', $request->pertemuan_id)
                ->where('jadwal_id', $jadwal->id)->exists(),
            422,
            'Pertemuan tidak sesuai dengan jadwal mata kuliah.'
        );

        $lampiran = null;

        if ($request->hasFile('lampiran')) {
            $nama = time().'_'.$request->file('lampiran')->getClientOriginalName();
            $lampiran = $request
                ->file('lampiran')
                ->storeAs(
                    'lms/tugas',
                    $nama,
                    'public'
                );
        }

        $tugas = LmsTugas::create([
            'jadwal_id' => $request->jadwal_id,
            'pertemuan_id' => $request->pertemuan_id,
            'dosen_id' => $dosen->dosen_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe' => $request->tipe,
            'deadline' => $request->deadline,
            'nilai_maksimal' => $request->nilai_maksimal,
            'lampiran' => $lampiran,
            'izinkan_terlambat' => $request->boolean('izinkan_terlambat'),
            'izinkan_upload_ulang' => $request->boolean('izinkan_upload_ulang'),
            'aktif' => true,
        ]);

        if ($tugas->tipe === 'pilihan_ganda') {
            return redirect()->route('dosen.lms.tugas.soal.manage', $tugas)
                ->with('success', 'Tugas PG berhasil dibuat. Silakan tambahkan soal A-E.');
        }

        return back()->with(
            'success',
            'Tugas berhasil dibuat.'
        );
    }

    private function jadwalMilikDosen($jadwalId): Jadwal
    {
        $dosen = Auth::guard('dosen')->user();

        return Jadwal::accessibleInLmsByDosen($dosen->dosen_id)->findOrFail($jadwalId);
    }

    private function pastikanMateriMilikDosen(LmsMateri $materi): void
    {
        $dosen = Auth::guard('dosen')->user();
        abort_unless((int) $materi->dosen_id === (int) $dosen->dosen_id, 403);
    }

    public function pengumpulanTugas(LmsTugas $tugas)
    {
        $dosen = Auth::guard('dosen')->user();

        /*
         * Dosen hanya boleh membuka tugas miliknya sendiri.
         */
        abort_unless(
            (int) $tugas->dosen_id === (int) $dosen->dosen_id,
            403
        );

        $tugas->load([
            'jadwal',
            'soal',
            'pengumpulan.mahasiswa',
            'pengumpulan.penilai',
        ]);

        if (! $tugas->jadwal) {
            return back()->with(
                'error',
                'Data jadwal tugas tidak ditemukan.'
            );
        }

        /*
         * Ambil seluruh mahasiswa yang mengambil mata kuliah
         * berdasarkan kurikulum dan kelas pada jadwal.
         */
        $kelasJadwal = strtolower((string) $tugas->jadwal->jenis_kelas);
        $peserta = Krs::with('mahasiswa')
            ->where('kurikulum_id', $tugas->jadwal->kurikulum_id)
            ->where('ta_id', $tugas->jadwal->ta_id)
            ->whereHas('mahasiswa', function ($query) use ($kelasJadwal) {
                if ($kelasJadwal === 'karyawan') {
                    $query->whereRaw('LOWER(kelas) = ?', ['karyawan']);
                } else {
                    $query->where(function ($kelas) {
                        $kelas->whereNull('kelas')
                            ->orWhereRaw('LOWER(kelas) != ?', ['karyawan']);
                    });
                }
            })
            ->get()
            ->pluck('mahasiswa')
            ->filter()
            ->unique('mahasiswa_id')
            ->sortBy(function ($mahasiswa) {
                return $mahasiswa->nama_mahasiswa
                    ?? $mahasiswa->nama
                    ?? '';
            })
            ->values();

        /*
         * Pengumpulan dikelompokkan berdasarkan mahasiswa_id
         * agar mudah dicocokkan dengan daftar peserta.
         */
        $pengumpulanByMahasiswa = $tugas->pengumpulan
            ->keyBy('mahasiswa_id');

        $daftarPeserta = $peserta->map(function ($mahasiswa) use ($pengumpulanByMahasiswa) {
            return (object) [
                'mahasiswa' => $mahasiswa,
                'pengumpulan' => $pengumpulanByMahasiswa->get(
                    $mahasiswa->mahasiswa_id
                ),
            ];
        });

        $totalPeserta = $daftarPeserta->count();

        $totalMengumpulkan = $daftarPeserta
            ->filter(fn ($data) => $data->pengumpulan !== null)
            ->count();

        $totalBelumMengumpulkan =
            $totalPeserta - $totalMengumpulkan;

        $totalDinilai = $daftarPeserta
            ->filter(function ($data) {
                return $data->pengumpulan !== null
                    && $data->pengumpulan->nilai !== null;
            })
            ->count();

        return view(
            'dosen.lms.pengumpulan-tugas',
            compact(
                'tugas',
                'daftarPeserta',
                'totalPeserta',
                'totalMengumpulkan',
                'totalBelumMengumpulkan',
                'totalDinilai'
            )
        );
    }

    public function downloadPengumpulan(
        LmsPengumpulanTugas $pengumpulan
    ) {
        $dosen = Auth::guard('dosen')->user();

        $pengumpulan->load('tugas');

        abort_unless(
            $pengumpulan->tugas
            && (int) $pengumpulan->tugas->dosen_id ===
            (int) $dosen->dosen_id,
            403
        );

        if (
            ! $pengumpulan->file ||
            ! StoredUpload::exists($pengumpulan->file)
        ) {
            return back()->with(
                'error',
                'File jawaban tidak ditemukan.'
            );
        }

        return StoredUpload::disk($pengumpulan->file)->download(
            $pengumpulan->file,
            basename($pengumpulan->file)
        );
    }

    public function previewPengumpulan(
        LmsPengumpulanTugas $pengumpulan
    ) {
        $dosen = Auth::guard('dosen')->user();

        $pengumpulan->load('tugas');

        abort_unless(
            $pengumpulan->tugas
            && (int) $pengumpulan->tugas->dosen_id === (int) $dosen->dosen_id,
            403
        );

        abort_unless(
            $pengumpulan->file
            && StoredUpload::exists($pengumpulan->file),
            404,
            'File jawaban tidak ditemukan.'
        );

        return response()->file(
            StoredUpload::disk($pengumpulan->file)->path($pengumpulan->file),
            [
                'Content-Disposition' => 'inline; filename="'.basename($pengumpulan->file).'"',
            ]
        );
    }

    public function nilaiPengumpulan(
        Request $request,
        LmsPengumpulanTugas $pengumpulan,
        GradebookKhsSyncService $syncService
    ) {
        $dosen = Auth::guard('dosen')->user();

        $pengumpulan->load('tugas');

        if (! $pengumpulan->tugas) {
            return back()->with(
                'error',
                'Data tugas tidak ditemukan.'
            );
        }

        /*
         * Dosen hanya boleh menilai pengumpulan dari tugasnya.
         */
        abort_unless(
            (int) $pengumpulan->tugas->dosen_id ===
            (int) $dosen->dosen_id,
            403
        );

        $request->validate([
            'nilai' => [
                'required',
                'numeric',
                'min:0',
                'max:'.$pengumpulan->tugas->nilai_maksimal,
            ],
            'feedback' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'nilai.required' => 'Nilai wajib diisi.',
            'nilai.numeric' => 'Nilai harus berupa angka.',
            'nilai.min' => 'Nilai tidak boleh kurang dari 0.',
            'nilai.max' => 'Nilai tidak boleh melebihi nilai maksimal tugas.',
            'feedback.max' => 'Feedback maksimal 5.000 karakter.',
        ]);

        $pengumpulan->update([
            'nilai' => $request->nilai,
            'dinilai_otomatis' => false,
            'feedback' => $request->has('feedback')
                ? $request->feedback
                : $pengumpulan->feedback,
            'dinilai_pada' => now(),
            'dinilai_oleh' => $dosen->dosen_id,
        ]);

        $syncService->sync($pengumpulan->tugas->jadwal);

        return back()->with(
            'success',
            'Nilai dan feedback berhasil disimpan.'
        );
    }

    /**
     * Menghapus data tugas beserta file lampiran dan file pengumpulan mahasiswa.
     */
    public function destroyTugas(LmsTugas $tugas)
    {
        $dosen = Auth::guard('dosen')->user();

        // 1. Otorisasi: Dosen hanya boleh menghapus tugas miliknya sendiri
        abort_unless((int) $tugas->dosen_id === (int) $dosen->dosen_id, 403);

        // 2. Hapus file lampiran tugas jika ada
        if ($tugas->lampiran && Storage::disk('public')->exists($tugas->lampiran)) {
            Storage::disk('public')->delete($tugas->lampiran);
        }

        // 3. Ambil dan hapus seluruh file jawaban yang diunggah mahasiswa untuk tugas ini
        $pengumpulanList = LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)->get();

        foreach ($pengumpulanList as $pengumpulan) {
            if (StoredUpload::exists($pengumpulan->file)) {
                StoredUpload::delete($pengumpulan->file);
            }
            // Hapus record pengumpulan mahasiswa
            $pengumpulan->delete();
        }

        // 4. Hapus data utama tugas
        $tugas->delete();

        return back()->with('success', 'Tugas dan seluruh file terkait berhasil dihapus.');
    }

    public function updateTugas(Request $request, LmsTugas $tugas)
    {
        $dosen = Auth::guard('dosen')->user();

        // 1. Otorisasi: Dosen hanya boleh mengubah tugas miliknya sendiri
        abort_unless((int) $tugas->dosen_id === (int) $dosen->dosen_id, 403);
        $request->merge([
            'tipe' => $request->input('tipe') ?: ($tugas->tipe ?: 'file'),
        ]);

        // 2. Validasi Input
        $request->validate([
            'judul' => 'required|max:255',
            'deskripsi' => 'nullable',
            'tipe' => ['required', Rule::in(['file', 'pilihan_ganda'])],
            'deadline' => 'required|date',
            'nilai_maksimal' => 'required|integer|min:1|max:1000',
            'lampiran' => 'nullable|file|max:51200',
        ]);

        if ($request->tipe !== $tugas->tipe && $tugas->pengumpulan()->exists()) {
            throw ValidationException::withMessages([
                'tipe' => 'Tipe tugas tidak dapat diubah karena sudah ada pengumpulan mahasiswa.',
            ]);
        }

        // 3. Penanganan File Lampiran Baru (jika ada)
        if ($request->hasFile('lampiran')) {
            // Hapus lampiran lama di storage jika ada
            if ($tugas->lampiran && Storage::disk('public')->exists($tugas->lampiran)) {
                Storage::disk('public')->delete($tugas->lampiran);
            }

            // Simpan lampiran baru
            $nama = time().'_'.$request->file('lampiran')->getClientOriginalName();
            $tugas->lampiran = $request->file('lampiran')->storeAs('lms/tugas', $nama, 'public');
        }

        // 4. Update data tugas
        $tugas->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe' => $request->tipe,
            'deadline' => $request->deadline,
            'nilai_maksimal' => $request->nilai_maksimal,
            'izinkan_terlambat' => $request->boolean('izinkan_terlambat'),
            'izinkan_upload_ulang' => $request->boolean('izinkan_upload_ulang'),
            'lampiran' => $tugas->lampiran,
        ]);

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function manageTugasSoal(LmsTugas $tugas)
    {
        $this->pastikanTugasMilikDosen($tugas);
        abort_unless($tugas->tipe === 'pilihan_ganda', 404);
        $tugas->load(['jadwal.kurikulum.mataKuliah', 'pertemuan', 'soal'])->loadCount('pengumpulan');

        return view('dosen.lms.tugas-soal', compact('tugas'));
    }

    public function storeTugasSoal(Request $request, LmsTugas $tugas)
    {
        $this->pastikanTugasMilikDosen($tugas);
        abort_unless($tugas->tipe === 'pilihan_ganda', 404);
        abort_if($tugas->pengumpulan()->exists(), 422, 'Soal tidak dapat ditambah karena tugas sudah dikerjakan mahasiswa.');
        $data = $this->validateTugasSoal($request);
        $data['tugas_id'] = $tugas->tugas_id;
        $data['urutan'] = $request->integer('urutan', ($tugas->soal()->max('urutan') ?? 0) + 1);
        LmsTugasSoal::create($data);

        return back()->with('success', 'Soal tugas berhasil ditambahkan.');
    }

    public function updateTugasSoal(Request $request, LmsTugasSoal $soal)
    {
        $this->pastikanTugasMilikDosen($soal->tugas);
        abort_if($soal->tugas->pengumpulan()->exists(), 422, 'Soal tidak dapat diubah karena tugas sudah dikerjakan mahasiswa.');
        $soal->update($this->validateTugasSoal($request));

        return back()->with('success', 'Soal tugas berhasil diperbarui.');
    }

    public function destroyTugasSoal(LmsTugasSoal $soal)
    {
        $this->pastikanTugasMilikDosen($soal->tugas);
        abort_if($soal->tugas->pengumpulan()->exists(), 422, 'Soal tidak dapat dihapus karena tugas sudah dikerjakan mahasiswa.');
        $soal->delete();

        return back()->with('success', 'Soal tugas berhasil dihapus.');
    }

    private function validateTugasSoal(Request $request): array
    {
        $data = $request->validate([
            'pertanyaan' => ['required', 'string'],
            'opsi' => ['required', 'array', 'size:5'],
            'opsi.*' => ['required', 'string', 'max:1000'],
            'kunci_jawaban' => ['required', 'integer', 'between:0,4'],
            'bobot' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ]);
        $data['opsi'] = array_values($data['opsi']);

        return $data;
    }

    private function pastikanTugasMilikDosen(LmsTugas $tugas): void
    {
        abort_unless((int) $tugas->dosen_id === (int) Auth::guard('dosen')->id(), 403);
    }
}
