<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsMateri; // <-- Pastikan Model LmsMateri atau Materi di-import
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use App\Services\LmsCalendarService;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LmsMahasiswaController extends Controller
{
    public function index(LmsCalendarService $calendar)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $kurikulumIds = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id)
            ->pluck('kurikulum_id');

        $jadwalList = Jadwal::query()
            ->where('ta_id', $activeTA->ta_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->when(strtolower((string) $mahasiswa->kelas) === 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['karyawan']))
            ->when(strtolower((string) $mahasiswa->kelas) !== 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['reguler']))
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah.dosen',
                'ruangan',
            ])
            ->withCount([
                'pertemuan',
                'materi' => fn ($query) => $query->where('status', 1),
                'tugas' => fn ($query) => $query->where('aktif', true),
                'quiz' => fn ($query) => $query->where('aktif', true),
            ])
            ->orderBy('hari')->orderBy('jam_mulai')->get();

        $calendarEvents = $calendar->events(
            $jadwalList->pluck('id'),
            'mahasiswa',
            'mahasiswa',
            (int) $mahasiswa->mahasiswa_id
        );

        return view('mahasiswa.lms.index', compact('jadwalList', 'activeTA', 'calendarEvents'));
    }

    public function show(Jadwal $jadwal)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($this->mahasiswaTerdaftarPadaJadwal($jadwal, $mahasiswa), 403,
            'Anda tidak terdaftar pada mata kuliah ini.');

        $jadwal->load(['kurikulum.mataKuliah', 'kurikulum.programStudi', 'ruangan']);
        $pertemuan = Pertemuan::where('jadwal_id', $jadwal->id)
            ->with([
                'materi' => fn ($query) => $query->where('status', 1)->orderBy('created_at'),
                'tugas' => fn ($query) => $query->where('aktif', true)
                    ->with(['pengumpulan' => fn ($query) => $query
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                    ->orderBy('deadline'),
            ])
            ->orderBy('tanggal_pertemuan')->orderBy('pertemuan_id')->get();
        $nomorPertemuan = $pertemuan->pluck('pertemuan_id')->flip()
            ->map(fn ($index) => (int) $index + 1);

        $materiList = LmsMateri::where('jadwal_id', $jadwal->id)
            ->where('status', 1)
            ->with('pertemuan')
            ->orderBy('pertemuan_id')
            ->orderBy('created_at')
            ->get();

        $tugasList = LmsTugas::where('jadwal_id', $jadwal->id)
            ->where('aktif', true)
            ->with([
                'pertemuan',
                'pengumpulan' => fn ($query) => $query
                    ->where('mahasiswa_id', $mahasiswa->mahasiswa_id),
            ])
            ->orderBy('pertemuan_id')
            ->orderBy('deadline')
            ->get();

        return view('mahasiswa.lms.show', compact(
            'jadwal',
            'pertemuan',
            'nomorPertemuan',
            'materiList',
            'tugasList'
        ));
    }

    public function gradebookIndex()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $kurikulumIds = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id)
            ->pluck('kurikulum_id');

        $jadwalList = Jadwal::where('ta_id', $activeTA->ta_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->when(strtolower((string) $mahasiswa->kelas) === 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['karyawan']))
            ->when(strtolower((string) $mahasiswa->kelas) !== 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['reguler']))
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'tugas' => fn ($query) => $query->where('aktif', true)
                    ->with(['pengumpulan' => fn ($pengumpulan) => $pengumpulan
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)]),
                'quiz' => fn ($query) => $query->where('aktif', true)
                    ->with([
                        'soal',
                        'attempts' => fn ($attempt) => $attempt
                            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id),
                    ]),
            ])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->each(function ($jadwal) {
                $maksimalQuiz = (float) $jadwal->quiz->sum(fn ($quiz) => $quiz->soal->sum('bobot'));
                $nilaiQuiz = (float) $jadwal->quiz->sum(function ($quiz) {
                    $attempt = $quiz->attempts->first();

                    return $attempt?->status === 'graded' ? (float) ($attempt->nilai_total ?? 0) : 0;
                });
                $jadwal->total_maksimal_gradebook = (float) $jadwal->tugas->sum('nilai_maksimal') + $maksimalQuiz;
                $jadwal->nilai_gradebook = (float) $jadwal->tugas->sum(function ($tugas) {
                    return (float) ($tugas->pengumpulan->first()?->nilai ?? 0);
                }) + $nilaiQuiz;
                $jadwal->tugas_dinilai_gradebook = $jadwal->tugas->filter(function ($tugas) {
                    return $tugas->pengumpulan->first()?->nilai !== null;
                })->count() + $jadwal->quiz->filter(function ($quiz) {
                    return $quiz->attempts->first()?->status === 'graded';
                })->count();
                $jadwal->persentase_gradebook = $jadwal->total_maksimal_gradebook > 0
                    ? round(($jadwal->nilai_gradebook / $jadwal->total_maksimal_gradebook) * 100, 1)
                    : 0;
            });

        return view('mahasiswa.lms.gradebook-index', compact('jadwalList', 'activeTA'));
    }

    public function gradebookShow(Jadwal $jadwal)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($this->mahasiswaTerdaftarPadaJadwal($jadwal, $mahasiswa), 403,
            'Anda tidak terdaftar pada mata kuliah ini.');

        $jadwal->load(['kurikulum.mataKuliah', 'kurikulum.programStudi']);
        $tugasList = LmsTugas::where('jadwal_id', $jadwal->id)
            ->where('aktif', true)
            ->with([
                'pertemuan',
                'pengumpulan' => fn ($query) => $query
                    ->where('mahasiswa_id', $mahasiswa->mahasiswa_id),
            ])
            ->orderBy('pertemuan_id')
            ->orderBy('deadline')
            ->get();
        $quizList = LmsQuiz::where('jadwal_id', $jadwal->id)
            ->where('aktif', true)
            ->with([
                'soal',
                'attempts' => fn ($query) => $query
                    ->where('mahasiswa_id', $mahasiswa->mahasiswa_id),
            ])->orderBy('deadline')->get()
            ->each(function ($quiz) {
                $quiz->nilai_maksimal_gradebook = (float) $quiz->soal->sum('bobot');
            });
        $nomorPertemuan = Pertemuan::where('jadwal_id', $jadwal->id)
            ->orderBy('tanggal_pertemuan')
            ->orderBy('pertemuan_id')
            ->pluck('pertemuan_id')
            ->flip()
            ->map(fn ($index) => (int) $index + 1);

        $totalMaksimal = (float) $tugasList->sum('nilai_maksimal')
            + (float) $quizList->sum('nilai_maksimal_gradebook');
        $nilaiDiperoleh = (float) $tugasList->sum(function ($tugas) {
            return (float) ($tugas->pengumpulan->first()?->nilai ?? 0);
        }) + (float) $quizList->sum(function ($quiz) {
            $attempt = $quiz->attempts->first();

            return $attempt?->status === 'graded' ? (float) ($attempt->nilai_total ?? 0) : 0;
        });
        $jumlahDinilai = $tugasList->filter(function ($tugas) {
            return $tugas->pengumpulan->first()?->nilai !== null;
        })->count() + $quizList->filter(function ($quiz) {
            return $quiz->attempts->first()?->status === 'graded';
        })->count();
        $persentase = $totalMaksimal > 0
            ? round(($nilaiDiperoleh / $totalMaksimal) * 100, 1)
            : 0;

        return view('mahasiswa.lms.gradebook-show', compact(
            'jadwal',
            'tugasList',
            'quizList',
            'totalMaksimal',
            'nilaiDiperoleh',
            'jumlahDinilai',
            'persentase',
            'nomorPertemuan'
        ));
    }

    /**
     * Menampilkan detail rekap absensi & daftar tugas/materi per pertemuan
     */
    public function detailRekap(Jadwal $jadwal)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (! $mahasiswa) {
            abort(401);
        }

        $pertemuan = Pertemuan::with([
            'materi', // Memuat relasi materi per pertemuan
            'tugas' => function ($query) use ($mahasiswa) {
                $query->where('aktif', true)
                    ->with([
                        'pengumpulan' => function ($q) use ($mahasiswa) {
                            $q->where('mahasiswa_id', $mahasiswa->mahasiswa_id);
                        },
                    ])
                    ->orderBy('deadline');
            },
            'absensi' => function ($query) use ($mahasiswa) {
                $query->where('mahasiswa_id', $mahasiswa->mahasiswa_id);
            },
        ])
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('pertemuan_id')
            ->get();

        // Hitung statistik absensi
        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpha = 0;

        foreach ($pertemuan as $item) {
            $status = strtolower(optional($item->absensi->first())->status ?? '');
            if ($status === 'hadir') {
                $hadir++;
            } elseif ($status === 'izin') {
                $izin++;
            } elseif ($status === 'sakit') {
                $sakit++;
            } elseif (in_array($status, ['alpha', 'tidak hadir'])) {
                $alpha++;
            }
        }

        $totalPertemuan = $pertemuan->count();
        $persentase = $totalPertemuan > 0
            ? round(($hadir / $totalPertemuan) * 100, 1)
            : 0;

        return view('mahasiswa.rekap-absensi-detail', compact(
            'jadwal',
            'pertemuan',
            'hadir',
            'izin',
            'sakit',
            'alpha',
            'persentase'
        ));
    }

    /**
     * Fitur Tambahan: Download File Materi LMS
     */
    public function downloadMateri($id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($mahasiswa, 401);

        // Sesuaikan nama Model Materi kamu (LmsMateri / Materi)
        $materi = LmsMateri::with('jadwal')->findOrFail($id);
        abort_unless($materi->jadwal && $this->mahasiswaTerdaftarPadaJadwal($materi->jadwal, $mahasiswa), 403);

        // Cek lokasi file di storage
        $filePath = $materi->file ?? $materi->file_path ?? $materi->path;

        if (! $filePath || ! Storage::disk('public')->exists($filePath)) {
            return back()->with('error', 'File materi tidak ditemukan di server.');
        }

        return Storage::disk('public')->download(
            $filePath,
            $materi->judul_materi ?? $materi->nama_materi ?? basename($filePath)
        );
    }

    public function showTugas(LmsTugas $tugas)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        abort_unless($mahasiswa, 401);
        abort_unless($tugas->aktif, 404);

        abort_unless(
            $this->mahasiswaTerdaftarPadaTugas(
                $tugas,
                $mahasiswa
            ),
            403,
            'Anda tidak terdaftar pada mata kuliah ini.'
        );

        $pengumpulan = LmsPengumpulanTugas::where(
            'tugas_id',
            $tugas->tugas_id
        )
            ->where(
                'mahasiswa_id',
                $mahasiswa->mahasiswa_id
            )
            ->first();

        $deadlineTerlewat = now()->greaterThan(
            $tugas->deadline
        );

        $bolehMengumpulkan =
            ! $deadlineTerlewat ||
            $tugas->izinkan_terlambat;

        $sudahDinilai = $this->pengumpulanSudahDinilai($pengumpulan);

        $bolehUploadUlang =
            ! $pengumpulan ||
            (! $sudahDinilai && $tugas->izinkan_upload_ulang);

        return view(
            'mahasiswa.lms.tugas-show',
            compact(
                'tugas',
                'pengumpulan',
                'deadlineTerlewat',
                'bolehMengumpulkan',
                'bolehUploadUlang',
                'sudahDinilai'
            )
        );
    }

    public function kumpulkanTugas(
        Request $request,
        LmsTugas $tugas
    ) {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (! $mahasiswa) {
            abort(401);
        }

        abort_unless(
            $this->mahasiswaTerdaftarPadaTugas(
                $tugas,
                $mahasiswa
            ),
            403,
            'Anda tidak terdaftar pada mata kuliah ini.'
        );

        abort_unless($tugas->aktif, 404);

        $pengumpulan = LmsPengumpulanTugas::where(
            'tugas_id',
            $tugas->tugas_id
        )
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->first();

        if ($this->pengumpulanSudahDinilai($pengumpulan)) {
            return back()->with(
                'error',
                'Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.'
            );
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
            ],
            'catatan' => 'nullable|string|max:2000',
        ], [
            'file.required' => 'File jawaban wajib dipilih.',
            'file.file' => 'Jawaban yang diunggah harus berupa file.',
            'file.max' => 'Ukuran file maksimal 50 MB.',
            'file.mimes' => 'Format file jawaban tidak didukung.',
            'catatan.max' => 'Catatan maksimal 2.000 karakter.',
        ]);

        $deadlineTerlewat = now()->greaterThan(
            $tugas->deadline
        );

        if (
            $deadlineTerlewat &&
            ! $tugas->izinkan_terlambat
        ) {
            return back()->with(
                'error',
                'Deadline pengumpulan telah berakhir.'
            );
        }

        if (
            $pengumpulan &&
            ! $tugas->izinkan_upload_ulang
        ) {
            return back()->with(
                'error',
                'Jawaban sudah dikumpulkan dan tidak dapat diganti.'
            );
        }

        if (
            $pengumpulan &&
            $pengumpulan->file &&
            StoredUpload::exists($pengumpulan->file)
        ) {
            StoredUpload::delete($pengumpulan->file);
        }

        $file = $request->file('file');

        $namaAsli = preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $file->getClientOriginalName()
        );

        $namaFile = now()->format('YmdHis')
            .'_'
            .$mahasiswa->mahasiswa_id
            .'_'
            .$namaAsli;

        $path = $file->storeAs(
            'lms/pengumpulan/'.$tugas->tugas_id,
            $namaFile,
            'private'
        );

        LmsPengumpulanTugas::updateOrCreate(
            [
                'tugas_id' => $tugas->tugas_id,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            ],
            [
                'file' => $path,
                'catatan' => $request->catatan,
                'waktu_upload' => now(),
            ]
        );

        return redirect()
            ->route('mahasiswa.lms.tugas.show', $tugas->tugas_id)
            ->with(
                'success',
                $pengumpulan
                ? 'Jawaban tugas berhasil diperbarui.'
                : 'Tugas berhasil dikumpulkan.'
            );
    }

    public function downloadPengumpulan(
        LmsPengumpulanTugas $pengumpulan
    ) {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (! $mahasiswa) {
            abort(401);
        }

        abort_unless(
            (int) $pengumpulan->mahasiswa_id ===
            (int) $mahasiswa->mahasiswa_id,
            403
        );

        if (
            ! $pengumpulan->file ||
            ! StoredUpload::exists($pengumpulan->file)
        ) {
            return back()->with('error', 'File jawaban tidak ditemukan.');
        }

        return StoredUpload::disk($pengumpulan->file)->download(
            $pengumpulan->file,
            basename($pengumpulan->file)
        );
    }

    private function pengumpulanSudahDinilai(?LmsPengumpulanTugas $pengumpulan): bool
    {
        return $pengumpulan !== null
            && ($pengumpulan->nilai !== null || $pengumpulan->dinilai_pada !== null);
    }

    private function mahasiswaTerdaftarPadaTugas(
        LmsTugas $tugas,
        $mahasiswa
    ): bool {
        $tugas->loadMissing('jadwal');

        if (! $tugas->jadwal) {
            return false;
        }

        return $this->mahasiswaTerdaftarPadaJadwal($tugas->jadwal, $mahasiswa);
    }

    private function mahasiswaTerdaftarPadaJadwal(Jadwal $jadwal, $mahasiswa): bool
    {
        $terdaftar = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->exists();
        $kelasMahasiswa = strtolower((string) $mahasiswa->kelas);
        $kelasJadwal = strtolower((string) $jadwal->jenis_kelas);

        return $terdaftar && ($kelasMahasiswa === 'karyawan'
            ? $kelasJadwal === 'karyawan'
            : $kelasJadwal === 'reguler');
    }

    public function showMateri($id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $materi = LmsMateri::with('jadwal')->findOrFail($id);
        abort_unless($materi->status && $materi->jadwal
            && $this->mahasiswaTerdaftarPadaJadwal($materi->jadwal, $mahasiswa), 403);

        if ($materi->youtube_url) {
            $url = str_starts_with($materi->youtube_url, 'http')
                ? $materi->youtube_url : 'https://'.$materi->youtube_url;

            return redirect()->away($url);
        }

        if ($materi->file && Storage::disk('public')->exists($materi->file)) {
            $fullPath = Storage::disk('public')->path($materi->file);

            return response()->file($fullPath);
        }

        return back()->with('error', 'File materi tidak ditemukan.');
    }
}
