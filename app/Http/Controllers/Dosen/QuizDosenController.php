<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizJawaban;
use App\Models\LmsQuizSoal;
use App\Models\Pertemuan;
use App\Services\GradebookKhsSyncService;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuizDosenController extends Controller
{
    public function index(Jadwal $jadwal)
    {
        $jadwal = $this->jadwalMilikDosen($jadwal->id);
        $jadwal->load(['kurikulum.mataKuliah', 'kurikulum.programStudi']);
        $pertemuan = Pertemuan::where('jadwal_id', $jadwal->id)
            ->orderBy('tanggal_pertemuan')->get();
        $quizList = LmsQuiz::where('jadwal_id', $jadwal->id)
            ->where('dosen_id', Auth::guard('dosen')->id())
            ->with('pertemuan')->withCount(['soal', 'attempts'])
            ->latest()->get();

        return view('dosen.lms.quiz.index', compact('jadwal', 'pertemuan', 'quizList'));
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $jadwal = $this->jadwalMilikDosen($jadwal->id);
        $data = $this->validateQuiz($request, $jadwal);
        $data['jadwal_id'] = $jadwal->id;
        $data['dosen_id'] = Auth::guard('dosen')->id();
        $data['aktif'] = $request->boolean('aktif');
        $quiz = LmsQuiz::create($data);

        return redirect()->route('dosen.lms.quiz.manage', $quiz)
            ->with('success', 'Quiz berhasil dibuat. Silakan tambahkan soal.');
    }

    public function update(Request $request, LmsQuiz $quiz)
    {
        $quiz = $this->quizMilikDosen($quiz);
        $data = $this->validateQuiz($request, $quiz->jadwal);
        $data['aktif'] = $request->boolean('aktif');
        $quiz->update($data);

        return back()->with('success', 'Pengaturan quiz berhasil diperbarui.');
    }

    public function destroy(LmsQuiz $quiz)
    {
        $quiz = $this->quizMilikDosen($quiz);
        $quizId = $quiz->quiz_id;
        $quizTitle = $quiz->judul;
        $jadwalId = $quiz->jadwal_id;
        $quiz->load('attempts.jawaban');
        foreach ($quiz->attempts->flatMap->jawaban as $jawaban) {
            if (StoredUpload::exists($jawaban->file)) {
                StoredUpload::delete($jawaban->file);
            }
        }
        $quiz->delete();

        activity_log('hapus_quiz', 'Dosen menghapus quiz '.$quizId.' ('.$quizTitle.') dari jadwal '.$jadwalId);

        return redirect()->route('dosen.lms.quiz.index', $jadwalId)
            ->with('success', 'Quiz dan seluruh jawabannya berhasil dihapus.');
    }

    public function manage(LmsQuiz $quiz)
    {
        $quiz = $this->quizMilikDosen($quiz);
        $quiz->load([
            'jadwal.kurikulum.mataKuliah', 'jadwal.kurikulum.programStudi',
            'pertemuan', 'soal',
        ])->loadCount('attempts');

        $destinationQuizzes = LmsQuiz::query()
            ->where('dosen_id', Auth::guard('dosen')->id())
            ->where('quiz_id', '!=', $quiz->quiz_id)
            ->whereDoesntHave('attempts')
            ->with(['jadwal.kurikulum.mataKuliah', 'jadwal.kurikulum.programStudi'])
            ->withCount('soal')
            ->latest()
            ->get();

        return view('dosen.lms.quiz.manage', compact('quiz', 'destinationQuizzes'));
    }

    public function storeSoal(Request $request, LmsQuiz $quiz)
    {
        $quiz = $this->quizMilikDosen($quiz);
        if ($quiz->attempts()->exists()) {
            return back()->with(
                'error',
                'Soal tidak dapat ditambahkan karena quiz sudah mulai dikerjakan mahasiswa.'
            );
        }
        $data = $this->validateSoal($request);
        $data['quiz_id'] = $quiz->quiz_id;
        $data['urutan'] = $request->integer('urutan', ($quiz->soal()->max('urutan') ?? 0) + 1);
        LmsQuizSoal::create($data);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function copySoal(Request $request, LmsQuiz $quiz)
    {
        $sourceQuiz = $this->quizMilikDosen($quiz);
        $sourceQuiz->load('soal');

        if ($sourceQuiz->soal->isEmpty()) {
            return back()->with('error', 'Quiz ini belum memiliki soal untuk disalin.');
        }

        $validated = $request->validate([
            'destination_quiz_id' => [
                'required',
                'integer',
                Rule::notIn([(int) $sourceQuiz->quiz_id]),
                'exists:lms_quiz,quiz_id',
            ],
        ], [
            'destination_quiz_id.required' => 'Pilih quiz tujuan penyalinan soal.',
            'destination_quiz_id.not_in' => 'Quiz tujuan harus berbeda dari quiz sumber.',
            'destination_quiz_id.exists' => 'Quiz tujuan tidak ditemukan.',
        ]);

        $destinationQuiz = LmsQuiz::query()->findOrFail($validated['destination_quiz_id']);
        $destinationQuiz = $this->quizMilikDosen($destinationQuiz);

        $copied = DB::transaction(function () use ($destinationQuiz, $sourceQuiz) {
            $lockedDestination = LmsQuiz::query()
                ->whereKey($destinationQuiz->quiz_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDestination->attempts()->exists()) {
                return null;
            }

            $nextOrder = ((int) $lockedDestination->soal()->max('urutan')) + 1;

            foreach ($sourceQuiz->soal as $sourceQuestion) {
                LmsQuizSoal::create([
                    'quiz_id' => $lockedDestination->quiz_id,
                    'tipe' => $sourceQuestion->tipe,
                    'pertanyaan' => $sourceQuestion->pertanyaan,
                    'opsi' => $sourceQuestion->opsi,
                    'kunci_jawaban' => $sourceQuestion->kunci_jawaban,
                    'bobot' => $sourceQuestion->bobot,
                    'urutan' => $nextOrder++,
                ]);
            }

            return $sourceQuiz->soal->count();
        });

        if ($copied === null) {
            return back()->with(
                'error',
                'Soal tidak dapat disalin karena quiz tujuan sudah mulai dikerjakan mahasiswa.'
            );
        }

        activity_log(
            'salin_soal_quiz',
            'Dosen menyalin '.$copied.' soal dari quiz '.$sourceQuiz->quiz_id.' ke quiz '.$destinationQuiz->quiz_id
        );

        return redirect()
            ->route('dosen.lms.quiz.manage', $destinationQuiz)
            ->with('success', $copied.' soal berhasil disalin dari quiz “'.$sourceQuiz->judul.'”.');
    }

    public function updateSoal(Request $request, LmsQuizSoal $soal)
    {
        $this->quizMilikDosen($soal->quiz);
        if ($soal->quiz->attempts()->exists()) {
            return back()->with(
                'error',
                'Soal tidak dapat diubah karena quiz sudah mulai dikerjakan mahasiswa.'
            );
        }
        $soal->update($this->validateSoal($request));

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroySoal(LmsQuizSoal $soal)
    {
        $this->quizMilikDosen($soal->quiz);
        if ($soal->quiz->attempts()->exists()) {
            return back()->with(
                'error',
                'Soal tidak dapat dihapus karena quiz sudah mulai dikerjakan mahasiswa.'
            );
        }
        $soal->delete();

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function hasil(LmsQuiz $quiz)
    {
        $quiz = $this->quizMilikDosen($quiz);
        $quiz->load(['jadwal.kurikulum.mataKuliah', 'soal', 'attempts.mahasiswa', 'attempts.jawaban.soal']);

        $kelasJadwal = strtolower((string) $quiz->jadwal->jenis_kelas);
        $peserta = Krs::with('mahasiswa')
            ->where('kurikulum_id', $quiz->jadwal->kurikulum_id)
            ->where('ta_id', $quiz->jadwal->ta_id)
            ->whereHas('mahasiswa', function ($query) use ($kelasJadwal) {
                $kelasJadwal === 'karyawan'
                    ? $query->whereRaw('LOWER(kelas) = ?', ['karyawan'])
                    : $query->where(fn ($kelas) => $kelas->whereNull('kelas')
                        ->orWhereRaw('LOWER(kelas) != ?', ['karyawan']));
            })->get()->pluck('mahasiswa')->filter()->unique('mahasiswa_id')
            ->sortBy('nama')->values();
        $attemptByMahasiswa = $quiz->attempts->keyBy('mahasiswa_id');

        return view('dosen.lms.quiz.hasil', compact('quiz', 'peserta', 'attemptByMahasiswa'));
    }

    public function nilai(
        Request $request,
        LmsQuizAttempt $attempt,
        GradebookKhsSyncService $syncService
    ) {
        $attempt->load(['quiz.soal', 'jawaban.soal']);
        $this->quizMilikDosen($attempt->quiz);
        abort_unless($attempt->status === 'submitted', 422, 'Jawaban tidak sedang menunggu penilaian.');

        $essayAnswers = $attempt->jawaban->filter(fn ($jawaban) => $jawaban->soal?->tipe === 'essay');
        foreach ($essayAnswers as $jawaban) {
            $request->validate([
                'nilai.'.$jawaban->jawaban_id => [
                    'required', 'numeric', 'min:0', 'max:'.$jawaban->soal->bobot,
                ],
                'feedback_jawaban.'.$jawaban->jawaban_id => 'nullable|string|max:3000',
            ]);
            $jawaban->update([
                'nilai' => $request->input('nilai.'.$jawaban->jawaban_id),
                'feedback' => $request->input('feedback_jawaban.'.$jawaban->jawaban_id),
            ]);
        }

        $nilaiEssay = (float) $essayAnswers->sum(fn ($jawaban) => (float) $jawaban->fresh()->nilai);
        $attempt->update([
            'status' => 'graded',
            'nilai_essay' => $nilaiEssay,
            'nilai_total' => (float) $attempt->nilai_pg + $nilaiEssay,
            'feedback' => $request->input('feedback'),
            'dinilai_at' => now(),
            'dinilai_oleh' => Auth::guard('dosen')->id(),
            'izinkan_ulang' => false,
        ]);

        $syncService->sync($attempt->quiz->jadwal);

        return back()->with('success', 'Nilai quiz berhasil disimpan dan jawaban mahasiswa dikunci.');
    }

    public function izinkanUlang(LmsQuizAttempt $attempt)
    {
        $attempt->load('quiz');
        $this->quizMilikDosen($attempt->quiz);
        abort_unless(in_array($attempt->status, ['submitted', 'graded']), 422);
        $attempt->update(['izinkan_ulang' => true, 'started_at' => now()]);

        return back()->with('success', 'Mahasiswa diizinkan memperbarui dan mengirim ulang jawaban.');
    }

    public function downloadJawaban(LmsQuizJawaban $jawaban)
    {
        $jawaban->load('attempt.quiz');
        $this->quizMilikDosen($jawaban->attempt->quiz);
        abort_unless(StoredUpload::exists($jawaban->file), 404);

        return StoredUpload::disk($jawaban->file)->download($jawaban->file, basename($jawaban->file));
    }

    private function validateQuiz(Request $request, Jadwal $jadwal): array
    {
        $data = $request->validate([
            'pertemuan_id' => 'nullable|exists:pertemuan,pertemuan_id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'mulai_at' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:mulai_at',
            'durasi_menit' => 'nullable|integer|min:1|max:1440',
        ]);
        if (! empty($data['pertemuan_id'])) {
            abort_unless(Pertemuan::where('pertemuan_id', $data['pertemuan_id'])
                ->where('jadwal_id', $jadwal->id)->exists(), 422, 'Pertemuan tidak sesuai jadwal.');
        }

        return $data;
    }

    private function validateSoal(Request $request): array
    {
        $data = $request->validate([
            'tipe' => ['required', Rule::in(['pilihan_ganda', 'essay'])],
            'pertanyaan' => 'required|string',
            'bobot' => 'required|numeric|min:0.01|max:1000',
            'urutan' => 'nullable|integer|min:1',
            'opsi' => 'nullable|array',
            'opsi.*' => 'nullable|string|max:1000',
            'kunci_jawaban' => 'nullable|string',
        ]);
        if ($data['tipe'] === 'pilihan_ganda') {
            $opsi = array_values(array_filter($data['opsi'] ?? [], fn ($opsi) => trim((string) $opsi) !== ''));
            if (count($opsi) < 2) {
                throw ValidationException::withMessages([
                    'opsi' => 'Soal pilihan ganda minimal memiliki dua opsi.',
                ]);
            }
            if (! isset($data['kunci_jawaban'])
                || ! array_key_exists((int) $data['kunci_jawaban'], $opsi)) {
                throw ValidationException::withMessages([
                    'kunci_jawaban' => 'Pilih kunci jawaban yang valid.',
                ]);
            }
            $data['opsi'] = $opsi;
            $data['kunci_jawaban'] = (string) (int) $data['kunci_jawaban'];
        } else {
            $data['opsi'] = null;
            $data['kunci_jawaban'] = null;
        }

        return $data;
    }

    private function jadwalMilikDosen($jadwalId): Jadwal
    {
        $dosen = Auth::guard('dosen')->user();

        return Jadwal::accessibleInLmsByDosen($dosen->dosen_id)->findOrFail($jadwalId);
    }

    private function quizMilikDosen(LmsQuiz $quiz): LmsQuiz
    {
        $jadwal = $this->jadwalMilikDosen($quiz->jadwal_id);
        abort_unless((int) $quiz->dosen_id === (int) Auth::guard('dosen')->id(), 403);
        $quiz->setRelation('jadwal', $jadwal);

        return $quiz;
    }
}
