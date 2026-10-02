<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\LmsMateri;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsTugas;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use App\Support\StoredUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LmsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $tahunAkademik = TahunAkademik::query()
            ->where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);

        if (! $tahunAkademik) {
            return response()->json([
                'tahun_akademik' => null,
                'kelas' => [],
            ]);
        }

        $jadwalIds = KrsClassResolver::jadwalIdsForMahasiswa(
            $mahasiswa,
            (int) $tahunAkademik->ta_id
        );

        $jadwal = Jadwal::query()
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
                'tugas' => fn ($query) => $query->where('aktif', true)
                    ->visibleForMahasiswa((int) $mahasiswa->mahasiswa_id),
                'quiz' => fn ($query) => $query->where('aktif', true),
            ])
            ->orderByRaw("FIELD(LOWER(hari), 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (Jadwal $item) => $this->schedulePayload($item));

        return response()->json([
            'tahun_akademik' => [
                'id' => (int) $tahunAkademik->ta_id,
                'nama' => $tahunAkademik->nama,
                'periode' => $tahunAkademik->semester,
            ],
            'kelas' => $jadwal,
        ]);
    }

    public function show(Request $request, Jadwal $jadwal): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->authorizeSchedule($jadwal, $mahasiswa);

        $jadwal->load([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'kurikulum.dosenToMatakuliah.dosen',
            'ruangan',
        ])->loadCount([
            'pertemuan',
            'materi' => fn ($query) => $query->where('status', 1),
            'tugas' => fn ($query) => $query->where('aktif', true)
                ->visibleForMahasiswa((int) $mahasiswa->mahasiswa_id),
            'quiz' => fn ($query) => $query->where('aktif', true),
        ]);

        $pertemuan = Pertemuan::query()
            ->where('jadwal_id', $jadwal->id)
            ->with([
                'dosen',
                'materi' => fn ($query) => $query
                    ->where('status', 1)
                    ->orderBy('created_at'),
                'tugas' => fn ($query) => $query
                    ->where('aktif', true)
                    ->visibleForMahasiswa((int) $mahasiswa->mahasiswa_id)
                    ->with(['pengumpulan' => fn ($submission) => $submission
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                    ->orderBy('deadline'),
                'quiz' => fn ($query) => $query
                    ->where('aktif', true)
                    ->with(['attempts' => fn ($attempt) => $attempt
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                    ->orderBy('deadline'),
            ])
            ->orderBy('tanggal_pertemuan')
            ->orderBy('pertemuan_id')
            ->get()
            ->values()
            ->map(function (Pertemuan $item, int $index) {
                return [
                    'id' => (int) $item->pertemuan_id,
                    'nomor' => $index + 1,
                    'tanggal' => $item->tanggal_pertemuan,
                    'topik' => $item->topik,
                    'sub_topik' => $item->sub_topik,
                    'metode_pbm' => $item->metode_pbm,
                    'jam_mulai' => $this->formatTime($item->jam_mulai),
                    'jam_selesai' => $this->formatTime($item->jam_selesai),
                    'dosen' => $item->dosen?->nama,
                    'materi' => $item->materi->map(fn (LmsMateri $materi) => [
                        'id' => (int) $materi->materi_id,
                        'judul' => $materi->judul,
                        'deskripsi' => $materi->deskripsi,
                        'tipe' => $materi->tipe,
                        'youtube_url' => $materi->youtube_url,
                        'punya_file' => StoredUpload::exists($materi->file),
                        'nama_file' => $materi->file ? basename($materi->file) : null,
                        'diunggah_pada' => $materi->created_at?->toIso8601String(),
                    ])->values(),
                    'tugas' => $item->tugas->map(function ($tugas) {
                        /** @var LmsPengumpulanTugas|null $submission */
                        $submission = $tugas->pengumpulan->first();

                        return [
                            'id' => (int) $tugas->tugas_id,
                            'judul' => $tugas->judul,
                            'deskripsi' => $tugas->deskripsi,
                            'tipe' => $tugas->tipe,
                            'cakupan' => $tugas->cakupan,
                            'tugas_individu' => $tugas->cakupan === 'individu',
                            'deadline' => $tugas->deadline?->toIso8601String(),
                            'nilai_maksimal' => (float) $tugas->nilai_maksimal,
                            'status_pengumpulan' => $submission
                                ? ($submission->nilai !== null ? 'dinilai' : 'dikumpulkan')
                                : 'belum_dikumpulkan',
                            'nilai' => $submission?->nilai !== null
                                ? (float) $submission->nilai
                                : null,
                        ];
                    })->values(),
                    'quiz' => $item->quiz->map(function ($quiz) {
                        $attempt = $quiz->attempts->first();

                        return [
                            'id' => (int) $quiz->quiz_id,
                            'judul' => $quiz->judul,
                            'deskripsi' => $quiz->deskripsi,
                            'mulai_at' => $quiz->mulai_at?->toIso8601String(),
                            'deadline' => $quiz->deadline?->toIso8601String(),
                            'durasi_menit' => (int) $quiz->durasi_menit,
                            'status_attempt' => $attempt?->status ?? 'belum_mulai',
                            'nilai' => $attempt?->nilai_total !== null
                                ? (float) $attempt->nilai_total
                                : null,
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'kelas' => $this->schedulePayload($jadwal),
            'pertemuan' => $pertemuan,
        ]);
    }

    public function materialFile(Request $request, LmsMateri $materi): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $materi->loadMissing('jadwal');

        abort_unless($materi->status && $materi->jadwal, 404);
        $this->authorizeSchedule($materi->jadwal, $mahasiswa);
        abort_unless(StoredUpload::exists($materi->file), 404, 'File materi tidak ditemukan.');

        return StoredUpload::disk($materi->file)->download(
            $materi->file,
            basename($materi->file)
        );
    }

    public function assignment(Request $request, LmsTugas $tugas): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->authorizeAssignment($tugas, $mahasiswa);
        $tugas->loadMissing(['soal', 'jadwal.kurikulum.mataKuliah']);

        $submission = LmsPengumpulanTugas::query()
            ->where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->first();
        $deadlinePassed = $tugas->deadline && now()->greaterThan($tugas->deadline);
        $graded = $this->isManuallyGraded($submission);
        $canSubmit = ! $submission
            ? (! $deadlinePassed || $tugas->izinkan_terlambat)
            : (! $deadlinePassed && ! $graded && $tugas->izinkan_upload_ulang);

        return response()->json([
            'tugas' => [
                'id' => (int) $tugas->tugas_id,
                'judul' => $tugas->judul,
                'deskripsi' => $tugas->deskripsi,
                'tipe' => $tugas->tipe,
                'cakupan' => $tugas->cakupan,
                'deadline' => $tugas->deadline?->toIso8601String(),
                'nilai_maksimal' => (float) $tugas->nilai_maksimal,
                'izinkan_terlambat' => (bool) $tugas->izinkan_terlambat,
                'izinkan_upload_ulang' => (bool) $tugas->izinkan_upload_ulang,
                'punya_lampiran' => StoredUpload::exists($tugas->lampiran),
                'nama_lampiran' => $tugas->lampiran ? basename($tugas->lampiran) : null,
                'mata_kuliah' => $tugas->jadwal?->kurikulum?->mataKuliah?->nama,
            ],
            'soal' => $tugas->tipe === 'pilihan_ganda'
                ? $tugas->soal->map(fn ($soal) => [
                    'id' => (int) $soal->soal_id,
                    'pertanyaan' => $soal->pertanyaan,
                    'opsi' => array_values($soal->opsi ?? []),
                    'bobot' => (float) $soal->bobot,
                ])->values()
                : [],
            'pengumpulan' => $submission ? [
                'id' => (int) $submission->pengumpulan_id,
                'catatan' => $submission->catatan,
                'jawaban_teks' => $submission->jawaban_teks,
                'jawaban_pg' => $submission->jawaban_pg ?? new \stdClass,
                'punya_file' => StoredUpload::exists($submission->file),
                'nama_file' => $submission->file ? basename($submission->file) : null,
                'waktu_upload' => $submission->waktu_upload?->toIso8601String(),
                'nilai' => $submission->nilai !== null ? (float) $submission->nilai : null,
                'dinilai_otomatis' => (bool) $submission->dinilai_otomatis,
                'feedback' => $submission->feedback,
            ] : null,
            'aturan' => [
                'deadline_terlewat' => (bool) $deadlinePassed,
                'sudah_dinilai' => $graded,
                'boleh_mengumpulkan' => $canSubmit,
                'alasan_terkunci' => $this->lockedReason($tugas, $submission, (bool) $deadlinePassed, $graded),
                'maksimal_upload_kb' => (int) config('lms.temporary_task_upload.max_kilobytes', 10240),
            ],
        ]);
    }

    public function assignmentAttachment(Request $request, LmsTugas $tugas): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->authorizeAssignment($tugas, $mahasiswa);
        abort_unless(StoredUpload::exists($tugas->lampiran), 404, 'Lampiran tugas tidak ditemukan.');

        return StoredUpload::disk($tugas->lampiran)->download(
            $tugas->lampiran,
            basename($tugas->lampiran)
        );
    }

    public function submissionFile(Request $request, LmsPengumpulanTugas $pengumpulan): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        abort_unless((int) $pengumpulan->mahasiswa_id === (int) $mahasiswa->mahasiswa_id, 403);
        abort_unless(StoredUpload::exists($pengumpulan->file), 404, 'File jawaban tidak ditemukan.');

        return StoredUpload::disk($pengumpulan->file)->download(
            $pengumpulan->file,
            basename($pengumpulan->file)
        );
    }

    public function submitAssignment(Request $request, LmsTugas $tugas): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->authorizeAssignment($tugas, $mahasiswa);

        $submission = LmsPengumpulanTugas::query()
            ->where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->first();
        $deadlinePassed = $tugas->deadline && now()->greaterThan($tugas->deadline);
        $graded = $this->isManuallyGraded($submission);

        abort_if($submission && $deadlinePassed, 422, 'Batas waktu pengumpulan telah berakhir. Jawaban tidak dapat diubah atau diunggah ulang.');
        abort_if($graded, 422, 'Jawaban tidak dapat diubah karena tugas sudah dinilai oleh dosen.');
        abort_if($deadlinePassed && ! $tugas->izinkan_terlambat, 422, 'Deadline pengumpulan telah berakhir.');
        abort_if($submission && ! $tugas->izinkan_upload_ulang, 422, 'Jawaban sudah dikumpulkan dan tidak dapat diganti.');

        $wasSubmitted = $submission !== null;
        if ($tugas->tipe === 'pilihan_ganda') {
            $this->submitMultipleChoice($request, $tugas, $mahasiswa, $submission);
        } elseif ($tugas->tipe === 'teks') {
            $this->submitText($request, $tugas, $mahasiswa, $submission);
        } else {
            $this->submitFile($request, $tugas, $mahasiswa, $submission);
        }

        return response()->json([
            'message' => $wasSubmitted
                ? 'Jawaban tugas berhasil diperbarui.'
                : 'Tugas berhasil dikumpulkan.',
        ]);
    }

    private function submitText(Request $request, LmsTugas $tugas, Mahasiswa $mahasiswa, ?LmsPengumpulanTugas $submission): void
    {
        $validated = $request->validate([
            'jawaban_teks' => ['required', 'string', 'max:50000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
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

        if ($submission?->file && StoredUpload::exists($submission->file)) {
            StoredUpload::delete($submission->file);
        }
    }

    private function submitMultipleChoice(Request $request, LmsTugas $tugas, Mahasiswa $mahasiswa, ?LmsPengumpulanTugas $submission): void
    {
        $tugas->loadMissing('soal');
        abort_if($tugas->soal->isEmpty(), 422, 'Tugas pilihan ganda belum memiliki soal.');
        $request->validate([
            'jawaban_pg' => ['required', 'array'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $answers = [];
        $correctWeight = 0;
        $totalWeight = (float) $tugas->soal->sum('bobot');
        foreach ($tugas->soal as $question) {
            $choice = $request->input('jawaban_pg.'.$question->soal_id);
            if ($choice === null || ! array_key_exists((int) $choice, $question->opsi ?? [])) {
                throw ValidationException::withMessages([
                    'jawaban_pg.'.$question->soal_id => 'Semua soal pilihan ganda wajib dijawab.',
                ]);
            }
            $answers[(string) $question->soal_id] = (int) $choice;
            if ((int) $choice === (int) $question->kunci_jawaban) {
                $correctWeight += (float) $question->bobot;
            }
        }

        $score = $totalWeight > 0
            ? round(($correctWeight / $totalWeight) * (float) $tugas->nilai_maksimal, 0)
            : 0;

        LmsPengumpulanTugas::updateOrCreate([
            'tugas_id' => $tugas->tugas_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ], [
            'file' => null,
            'catatan' => $request->input('catatan'),
            'jawaban_pg' => $answers,
            'jawaban_teks' => null,
            'waktu_upload' => now(),
            'nilai' => $score,
            'dinilai_otomatis' => true,
            'feedback' => null,
            'dinilai_pada' => now(),
            'dinilai_oleh' => null,
        ]);

        if ($submission?->file && StoredUpload::exists($submission->file)) {
            StoredUpload::delete($submission->file);
        }
    }

    private function submitFile(Request $request, LmsTugas $tugas, Mahasiswa $mahasiswa, ?LmsPengumpulanTugas $submission): void
    {
        $maxKb = (int) config('lms.temporary_task_upload.max_kilobytes', 10240);
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:'.$maxKb, 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('file');
        $original = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $original = ltrim((string) $original, '.');
        $name = now()->format('YmdHis').'_'.$mahasiswa->mahasiswa_id.'_'.Str::lower(Str::random(8)).'_'.($original ?: 'jawaban');
        $path = $file->storeAs('lms/pengumpulan/'.$tugas->tugas_id, $name, 'private');
        abort_unless($path !== false, 500, 'File jawaban gagal disimpan.');

        try {
            LmsPengumpulanTugas::updateOrCreate([
                'tugas_id' => $tugas->tugas_id,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            ], [
                'file' => $path,
                'catatan' => $validated['catatan'] ?? null,
                'jawaban_pg' => null,
                'jawaban_teks' => null,
                'waktu_upload' => now(),
                'nilai' => null,
                'dinilai_otomatis' => false,
                'feedback' => null,
                'dinilai_pada' => null,
                'dinilai_oleh' => null,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('private')->delete($path);
            throw $exception;
        }

        if ($submission?->file && $submission->file !== $path && StoredUpload::exists($submission->file)) {
            StoredUpload::delete($submission->file);
        }
    }

    private function authorizeAssignment(LmsTugas $tugas, Mahasiswa $mahasiswa): void
    {
        abort_unless($tugas->aktif, 404);
        $tugas->loadMissing('jadwal');
        abort_unless($tugas->jadwal, 404);
        $this->authorizeSchedule($tugas->jadwal, $mahasiswa);
        abort_unless($tugas->ditujukanKepada((int) $mahasiswa->mahasiswa_id), 403, 'Tugas ini tidak ditujukan kepada Anda.');
    }

    private function isManuallyGraded(?LmsPengumpulanTugas $submission): bool
    {
        return $submission !== null
            && ! $submission->dinilai_otomatis
            && ($submission->nilai !== null || $submission->dinilai_pada !== null);
    }

    private function lockedReason(LmsTugas $tugas, ?LmsPengumpulanTugas $submission, bool $deadlinePassed, bool $graded): ?string
    {
        if ($graded) {
            return 'Tugas sudah dinilai oleh dosen dan tidak dapat diubah.';
        }
        if ($submission && $deadlinePassed) {
            return 'Deadline telah berakhir. Jawaban tidak dapat diubah.';
        }
        if ($deadlinePassed && ! $tugas->izinkan_terlambat) {
            return 'Deadline pengumpulan telah berakhir.';
        }
        if ($submission && ! $tugas->izinkan_upload_ulang) {
            return 'Dosen tidak mengizinkan penggantian jawaban.';
        }

        return null;
    }

    private function authorizeSchedule(Jadwal $jadwal, Mahasiswa $mahasiswa): void
    {
        $tahunAkademik = TahunAkademik::query()
            ->where('status_ta', 1)
            ->value('ta_id');

        abort_unless(
            $tahunAkademik
            && (int) $jadwal->ta_id === (int) $tahunAkademik
            && KrsClassResolver::jadwalIdsForMahasiswa($mahasiswa, (int) $tahunAkademik)
                ->contains((int) $jadwal->id),
            403,
            'Anda tidak terdaftar pada kelas LMS ini.'
        );
    }

    private function schedulePayload(Jadwal $jadwal): array
    {
        $assignments = $jadwal->kurikulum?->dosenToMatakuliah ?? collect();
        $lecturers = $assignments
            ->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === 'teori'
                && KrsClassResolver::normalize($assignment->jenis_kelas)
                    === KrsClassResolver::normalize($jadwal->jenis_kelas))
            ->pluck('dosen.nama')
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => (int) $jadwal->id,
            'kode' => $jadwal->kurikulum?->mataKuliah?->matakuliah_id,
            'nama' => $jadwal->kurikulum?->mataKuliah?->nama ?? '-',
            'sks' => (int) ($jadwal->kurikulum?->mataKuliah?->sks ?? 0),
            'semester' => (int) ($jadwal->kurikulum?->mataKuliah?->smt ?? 0),
            'program_studi' => $jadwal->kurikulum?->programStudi?->nama,
            'kelas' => jenis_kelas_label($jadwal->jenis_kelas),
            'hari' => ucfirst((string) $jadwal->hari),
            'jam_mulai' => $this->formatTime($jadwal->jam_mulai),
            'jam_selesai' => $this->formatTime($jadwal->jam_selesai),
            'ruangan' => $jadwal->ruangan?->nama,
            'dosen' => $lecturers,
            'jumlah_pertemuan' => (int) ($jadwal->pertemuan_count ?? 0),
            'jumlah_materi' => (int) ($jadwal->materi_count ?? 0),
            'jumlah_tugas' => (int) ($jadwal->tugas_count ?? 0),
            'jumlah_quiz' => (int) ($jadwal->quiz_count ?? 0),
        ];
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
