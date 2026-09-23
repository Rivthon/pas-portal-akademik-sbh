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
use App\Support\KrsClassResolver;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LmsMahasiswaController extends Controller
{
    public function index(LmsCalendarService $calendar)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $jadwalIds = KrsClassResolver::jadwalIdsForMahasiswa($mahasiswa, (int) $activeTA->ta_id);

        $jadwalList = Jadwal::query()
            ->whereIn('id', $jadwalIds)
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

        $jadwalIds = KrsClassResolver::jadwalIdsForMahasiswa($mahasiswa, (int) $activeTA->ta_id);

        $jadwalList = Jadwal::whereIn('id', $jadwalIds)
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

        $totalSudahDiabsen = $hadir + $izin + $sakit + $alpha;
        $persentase = $totalSudahDiabsen > 0
            ? round(($hadir / $totalSudahDiabsen) * 100, 1)
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
        $tugas->load(['soal', 'jadwal.kurikulum.mataKuliah']);

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

        $bolehUploadUlang = ! $pengumpulan || (
            ! $deadlineTerlewat
            && ! $sudahDinilai
            && $tugas->izinkan_upload_ulang
        );

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

    public function showLampiranTugas(LmsTugas $tugas)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        abort_unless($mahasiswa, 401);
        abort_unless($tugas->aktif, 404);
        abort_unless(
            $this->mahasiswaTerdaftarPadaTugas($tugas, $mahasiswa),
            403,
            'Anda tidak terdaftar pada mata kuliah ini.'
        );

        if (! $tugas->lampiran || ! Storage::disk('public')->exists($tugas->lampiran)) {
            return back()->with('error', 'File lampiran tugas tidak ditemukan di server.');
        }

        return Storage::disk('public')->response(
            $tugas->lampiran,
            basename($tugas->lampiran)
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

        $deadlineTerlewat = now()->greaterThan($tugas->deadline);

        if ($pengumpulan && $deadlineTerlewat) {
            return back()->with(
                'error',
                'Batas waktu pengumpulan telah berakhir. Jawaban tidak dapat diubah atau diunggah ulang.'
            );
        }

        if ($this->pengumpulanSudahDinilai($pengumpulan)) {
            return back()->with(
                'error',
                'Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.'
            );
        }

        if (
            $deadlineTerlewat &&
            ! $tugas->izinkan_terlambat
        ) {
            return back()->with(
                'error',
                'Deadline pengumpulan telah berakhir.'
            );
        }

        if ($tugas->tipe === 'pilihan_ganda') {
            return $this->kumpulkanTugasPilihanGanda($request, $tugas, $mahasiswa, $pengumpulan);
        }

        if ($tugas->tipe === 'teks') {
            return $this->kumpulkanTugasTeks($request, $tugas, $mahasiswa, $pengumpulan);
        }

        $maxUploadKilobytes = (int) config('lms.temporary_task_upload.max_kilobytes', 10240);
        $request->validate([
            'temporary_upload_token' => ['nullable', 'required_without:file', 'uuid'],
            'file' => [
                'nullable',
                'required_without:temporary_upload_token',
                'file',
                'max:'.$maxUploadKilobytes,
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
            ],
            'catatan' => 'nullable|string|max:2000',
        ], [
            'file.required_without' => 'File jawaban wajib dipilih.',
            'temporary_upload_token.required_without' => 'File jawaban wajib dipilih.',
            'file.file' => 'Jawaban yang diunggah harus berupa file.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.mimes' => 'Format file jawaban tidak didukung.',
            'catatan.max' => 'Catatan maksimal 2.000 karakter.',
        ]);

        if (
            $pengumpulan &&
            ! $tugas->izinkan_upload_ulang
        ) {
            return back()->with(
                'error',
                'Jawaban sudah dikumpulkan dan tidak dapat diganti.'
            );
        }

        $temporaryToken = $request->string('temporary_upload_token')->toString();
        $temporaryUpload = null;

        if ($temporaryToken !== '') {
            $temporaryUpload = $request->session()->get('lms_temporary_task_uploads.'.$temporaryToken);

            abort_unless(
                is_array($temporaryUpload)
                && (int) ($temporaryUpload['mahasiswa_id'] ?? 0) === (int) $mahasiswa->mahasiswa_id
                && (int) ($temporaryUpload['tugas_id'] ?? 0) === (int) $tugas->tugas_id
                && now()->timestamp <= (int) ($temporaryUpload['expires_at'] ?? 0)
                && str_starts_with(
                    (string) ($temporaryUpload['path'] ?? ''),
                    'lms/tmp-pengumpulan/'.$mahasiswa->mahasiswa_id.'/'.$tugas->tugas_id.'/'
                )
                && Storage::disk('private')->exists($temporaryUpload['path']),
                422,
                'Upload sementara sudah kedaluwarsa. Silakan pilih ulang file jawaban.'
            );

            $namaAsli = (string) $temporaryUpload['original_name'];
        } else {
            $file = $request->file('file');
            $namaAsli = $file->getClientOriginalName();
        }

        $namaAsli = preg_replace('/[^A-Za-z0-9._-]/', '_', $namaAsli);
        $namaAsli = ltrim((string) $namaAsli, '.');
        $namaAsli = $namaAsli !== '' ? $namaAsli : 'jawaban';

        $namaFile = now()->format('YmdHis')
            .'_'
            .$mahasiswa->mahasiswa_id
            .'_'
            .Str::lower(Str::random(8))
            .'_'
            .$namaAsli;

        $path = 'lms/pengumpulan/'.$tugas->tugas_id.'/'.$namaFile;

        if ($temporaryUpload) {
            Storage::disk('private')->makeDirectory('lms/pengumpulan/'.$tugas->tugas_id);
            abort_unless(
                Storage::disk('private')->move($temporaryUpload['path'], $path),
                500,
                'File sementara gagal dipindahkan. Silakan coba kembali.'
            );
        } else {
            $storedPath = $request->file('file')->storeAs(
                'lms/pengumpulan/'.$tugas->tugas_id,
                $namaFile,
                'private'
            );
            abort_unless($storedPath !== false, 500, 'File jawaban gagal disimpan.');
            $path = $storedPath;
        }

        try {
            LmsPengumpulanTugas::updateOrCreate(
                [
                    'tugas_id' => $tugas->tugas_id,
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                ],
                [
                    'file' => $path,
                    'catatan' => $request->catatan,
                    'jawaban_pg' => null,
                    'jawaban_teks' => null,
                    'waktu_upload' => now(),
                    'dinilai_otomatis' => false,
                ]
            );
        } catch (\Throwable $exception) {
            Storage::disk('private')->delete($path);
            throw $exception;
        }

        if ($temporaryToken !== '') {
            $request->session()->forget('lms_temporary_task_uploads.'.$temporaryToken);
        }

        if (
            $pengumpulan &&
            $pengumpulan->file &&
            $pengumpulan->file !== $path &&
            StoredUpload::exists($pengumpulan->file)
        ) {
            StoredUpload::delete($pengumpulan->file);
        }

        return redirect()
            ->route('mahasiswa.lms.tugas.show', $tugas->tugas_id)
            ->with(
                'success',
                $pengumpulan
                ? 'Jawaban tugas berhasil diperbarui.'
                : 'Tugas berhasil dikumpulkan.'
            );
    }

    public function uploadTugasSementara(Request $request, LmsTugas $tugas)
    {
        abort_unless(config('lms.temporary_task_upload.enabled', true), 404);

        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($mahasiswa, 401);
        abort_unless(
            $this->mahasiswaTerdaftarPadaTugas($tugas, $mahasiswa),
            403,
            'Anda tidak terdaftar pada mata kuliah ini.'
        );
        abort_unless($tugas->aktif && $tugas->tipe === 'file', 404);

        $pengumpulan = LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->first();
        $deadlineTerlewat = now()->greaterThan($tugas->deadline);

        abort_if(
            $pengumpulan && $deadlineTerlewat,
            422,
            'Batas waktu pengumpulan telah berakhir. Jawaban tidak dapat diubah atau diunggah ulang.'
        );
        abort_if(
            $this->pengumpulanSudahDinilai($pengumpulan),
            422,
            'Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.'
        );
        abort_if(
            $deadlineTerlewat && ! $tugas->izinkan_terlambat,
            422,
            'Deadline pengumpulan telah berakhir.'
        );
        abort_if(
            $pengumpulan && ! $tugas->izinkan_upload_ulang,
            422,
            'Jawaban sudah dikumpulkan dan tidak dapat diganti.'
        );

        $maxUploadKilobytes = (int) config('lms.temporary_task_upload.max_kilobytes', 10240);
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.$maxUploadKilobytes,
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
            ],
        ], [
            'file.required' => 'File jawaban wajib dipilih.',
            'file.file' => 'Jawaban yang diunggah harus berupa file.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.mimes' => 'Format file jawaban tidak didukung.',
        ]);

        $file = $request->file('file');
        $originalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $originalName = ltrim((string) $originalName, '.');
        $originalName = $originalName !== '' ? $originalName : 'jawaban';
        $token = (string) Str::uuid();
        $expiresAt = now()->addMinutes(
            (int) config('lms.temporary_task_upload.expires_minutes', 120)
        );
        $path = $file->storeAs(
            'lms/tmp-pengumpulan/'.$mahasiswa->mahasiswa_id.'/'.$tugas->tugas_id,
            $token.'_'.$originalName,
            'private'
        );

        abort_unless($path !== false, 500, 'File sementara gagal disimpan.');

        $request->session()->put('lms_temporary_task_uploads.'.$token, [
            'mahasiswa_id' => (int) $mahasiswa->mahasiswa_id,
            'tugas_id' => (int) $tugas->tugas_id,
            'path' => $path,
            'original_name' => $originalName,
            'expires_at' => $expiresAt->timestamp,
        ]);

        return response()->json([
            'token' => $token,
            'name' => $originalName,
            'size' => (int) $file->getSize(),
            'expires_at' => $expiresAt->toIso8601String(),
            'message' => 'File siap dikumpulkan.',
        ]);
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
            && ! $pengumpulan->dinilai_otomatis
            && ($pengumpulan->nilai !== null || $pengumpulan->dinilai_pada !== null);
    }

    private function kumpulkanTugasPilihanGanda(Request $request, LmsTugas $tugas, $mahasiswa, ?LmsPengumpulanTugas $pengumpulan)
    {
        $tugas->load('soal');
        abort_if($tugas->soal->isEmpty(), 422, 'Tugas pilihan ganda belum memiliki soal.');

        $request->validate([
            'jawaban_pg' => ['required', 'array'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $jawaban = [];
        $bobotBenar = 0;
        $totalBobot = (float) $tugas->soal->sum('bobot');
        foreach ($tugas->soal as $soal) {
            $pilihan = $request->input('jawaban_pg.'.$soal->soal_id);
            if ($pilihan === null || ! array_key_exists((int) $pilihan, $soal->opsi ?? [])) {
                throw ValidationException::withMessages([
                    'jawaban_pg.'.$soal->soal_id => 'Semua soal pilihan ganda wajib dijawab.',
                ]);
            }
            $jawaban[(string) $soal->soal_id] = (int) $pilihan;
            if ((int) $pilihan === (int) $soal->kunci_jawaban) {
                $bobotBenar += (float) $soal->bobot;
            }
        }

        $nilai = $totalBobot > 0
            ? round(($bobotBenar / $totalBobot) * (float) $tugas->nilai_maksimal, 0)
            : 0;

        LmsPengumpulanTugas::updateOrCreate([
            'tugas_id' => $tugas->tugas_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ], [
            'file' => null,
            'catatan' => $request->catatan,
            'jawaban_pg' => $jawaban,
            'jawaban_teks' => null,
            'waktu_upload' => now(),
            'nilai' => $nilai,
            'dinilai_otomatis' => true,
            'feedback' => null,
            'dinilai_pada' => now(),
            'dinilai_oleh' => null,
        ]);

        return redirect()->route('mahasiswa.lms.tugas.show', $tugas)
            ->with('success', $pengumpulan
                ? 'Jawaban pilihan ganda berhasil diperbarui.'
                : 'Jawaban pilihan ganda berhasil dikumpulkan dan dinilai otomatis.');
    }

    private function kumpulkanTugasTeks(
        Request $request,
        LmsTugas $tugas,
        $mahasiswa,
        ?LmsPengumpulanTugas $pengumpulan
    ) {
        $validated = $request->validate([
            'jawaban_teks' => ['required', 'string', 'max:50000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ], [
            'jawaban_teks.required' => 'Jawaban teks wajib diisi.',
            'jawaban_teks.max' => 'Jawaban teks maksimal 50.000 karakter.',
            'catatan.max' => 'Catatan maksimal 2.000 karakter.',
        ]);

        LmsPengumpulanTugas::updateOrCreate([
            'tugas_id' => $tugas->tugas_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ], [
            'file' => null,
            'catatan' => $validated['catatan'] ?? null,
            'jawaban_pg' => null,
            'jawaban_teks' => $validated['jawaban_teks'],
            'waktu_upload' => now(),
            'nilai' => null,
            'dinilai_otomatis' => false,
            'feedback' => null,
            'dinilai_pada' => null,
            'dinilai_oleh' => null,
        ]);

        return redirect()->route('mahasiswa.lms.tugas.show', $tugas)
            ->with(
                'success',
                $pengumpulan
                    ? 'Jawaban teks berhasil diperbarui.'
                    : 'Jawaban teks berhasil dikumpulkan.'
            );
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
        $krs = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->first();

        return $krs && KrsClassResolver::matches($krs, $jadwal, $mahasiswa);
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
