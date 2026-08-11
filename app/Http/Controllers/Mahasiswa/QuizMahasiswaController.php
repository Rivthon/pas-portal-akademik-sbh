<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizJawaban;
use App\Services\GradebookKhsSyncService;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizMahasiswaController extends Controller
{
    public function index(Jadwal $jadwal)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($this->terdaftar($jadwal, $mahasiswa), 403);
        $jadwal->load('kurikulum.mataKuliah');
        $quizList = LmsQuiz::where('jadwal_id', $jadwal->id)->where('aktif', true)
            ->withCount('soal')
            ->with(['attempts' => fn ($query) => $query->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
            ->orderBy('deadline')->get();

        return view('mahasiswa.lms.quiz.index', compact('jadwal', 'quizList'));
    }

    public function show(LmsQuiz $quiz)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $quiz->load(['jadwal.kurikulum.mataKuliah', 'soal']);
        abort_unless($quiz->aktif && $this->terdaftar($quiz->jadwal, $mahasiswa), 403);
        $attempt = LmsQuizAttempt::with('jawaban.soal')
            ->where('quiz_id', $quiz->quiz_id)->where('mahasiswa_id', $mahasiswa->mahasiswa_id)->first();
        $belumMulai = $quiz->mulai_at && now()->lt($quiz->mulai_at);
        $lewatDeadline = $quiz->deadline && now()->gt($quiz->deadline);
        $tidakAdaSoal = $quiz->soal->isEmpty();
        if (! $attempt && ! $tidakAdaSoal && ! $belumMulai && ! $lewatDeadline) {
            $attempt = LmsQuizAttempt::create([
                'quiz_id' => $quiz->quiz_id,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'status' => 'draft',
                'started_at' => now(),
            ]);
        }
        if ($attempt) {
            $quiz->setRelation('soal', $this->soalUntukAttempt($quiz, $attempt));
        }
        $jawabanBySoal = $attempt?->jawaban?->keyBy('soal_id') ?? collect();
        $durasiBerakhir = $attempt && $quiz->durasi_menit && $attempt->started_at
            && now()->gt($attempt->started_at->copy()->addMinutes($quiz->durasi_menit));
        $bolehEdit = ! $tidakAdaSoal && ! $belumMulai && ! $durasiBerakhir
            && (! $lewatDeadline || $attempt?->izinkan_ulang)
            && (! $attempt || $attempt->status === 'draft' || $attempt->izinkan_ulang);

        return view('mahasiswa.lms.quiz.show', compact(
            'quiz', 'attempt', 'jawabanBySoal', 'tidakAdaSoal', 'belumMulai',
            'lewatDeadline', 'durasiBerakhir', 'bolehEdit'
        ));
    }

    public function submit(
        Request $request,
        LmsQuiz $quiz,
        GradebookKhsSyncService $syncService
    ) {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $quiz->load(['jadwal', 'soal']);
        abort_unless($quiz->aktif && $this->terdaftar($quiz->jadwal, $mahasiswa), 403);
        abort_if($quiz->soal->isEmpty(), 422, 'Quiz belum memiliki soal.');
        abort_if($quiz->mulai_at && now()->lt($quiz->mulai_at), 422, 'Quiz belum dimulai.');

        $attempt = LmsQuizAttempt::firstOrNew([
            'quiz_id' => $quiz->quiz_id, 'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ]);
        $bolehUlang = $attempt->exists && $attempt->izinkan_ulang;
        abort_if($quiz->deadline && now()->gt($quiz->deadline) && ! $bolehUlang, 422, 'Deadline quiz telah berakhir.');
        abort_if($attempt->exists && $quiz->durasi_menit && $attempt->started_at
            && now()->gt($attempt->started_at->copy()->addMinutes($quiz->durasi_menit)),
            422, 'Durasi pengerjaan quiz telah berakhir.');
        abort_if($attempt->exists && $attempt->status !== 'draft' && ! $bolehUlang, 422,
            'Jawaban sudah dikirim dan terkunci. Hubungi dosen untuk izin pengerjaan ulang.');

        $request->validate([
            'pilihan' => 'nullable|array', 'jawaban_text' => 'nullable|array',
            'jawaban_text.*' => 'nullable|string|max:20000',
            'jawaban_file' => 'nullable|array',
            'jawaban_file.*' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
        ]);

        $existingBySoal = $attempt->exists
            ? $attempt->jawaban()->get()->keyBy('soal_id')
            : collect();
        foreach ($quiz->soal as $soal) {
            if ($soal->tipe === 'pilihan_ganda') {
                $pilihan = $request->input('pilihan.'.$soal->soal_id);
                if ($pilihan === null || ! array_key_exists((int) $pilihan, $soal->opsi ?? [])) {
                    throw ValidationException::withMessages([
                        'pilihan.'.$soal->soal_id => 'Semua soal pilihan ganda wajib dijawab.',
                    ]);
                }
            } else {
                $text = trim((string) $request->input('jawaban_text.'.$soal->soal_id, ''));
                $file = $request->file('jawaban_file.'.$soal->soal_id);
                if ($text === '' && ! $file && ! $existingBySoal->get($soal->soal_id)?->file) {
                    throw ValidationException::withMessages([
                        'jawaban_text.'.$soal->soal_id => 'Jawaban essay wajib ditulis atau diunggah.',
                    ]);
                }
            }
        }

        DB::transaction(function () use (
            $request, $quiz, $mahasiswa, $attempt, $existingBySoal
        ) {
            if (! $attempt->exists) {
                $attempt->fill(['status' => 'draft', 'started_at' => now()]);
                $attempt->save();
            }
            $nilaiPg = 0;
            $adaEssay = false;

            foreach ($quiz->soal as $soal) {
                $existing = $existingBySoal->get($soal->soal_id);
                $payload = ['attempt_id' => $attempt->attempt_id, 'soal_id' => $soal->soal_id];

                if ($soal->tipe === 'pilihan_ganda') {
                    $pilihan = $request->input('pilihan.'.$soal->soal_id);
                    if ($pilihan === null || ! array_key_exists((int) $pilihan, $soal->opsi ?? [])) {
                        throw ValidationException::withMessages([
                            'pilihan.'.$soal->soal_id => 'Semua soal pilihan ganda wajib dijawab.',
                        ]);
                    }
                    $nilai = (string) $pilihan === (string) $soal->kunci_jawaban ? (float) $soal->bobot : 0;
                    $nilaiPg += $nilai;
                    $payload += ['pilihan_jawaban' => (string) $pilihan, 'jawaban_text' => null, 'nilai' => $nilai, 'feedback' => null];
                } else {
                    $adaEssay = true;
                    $text = trim((string) $request->input('jawaban_text.'.$soal->soal_id, ''));
                    $file = $request->file('jawaban_file.'.$soal->soal_id);
                    if ($text === '' && ! $file && ! $existing?->file) {
                        throw ValidationException::withMessages([
                            'jawaban_text.'.$soal->soal_id => 'Jawaban essay wajib ditulis atau diunggah.',
                        ]);
                    }
                    $payload['jawaban_text'] = $text !== '' ? $text : null;
                    $payload['pilihan_jawaban'] = null;
                    $payload['nilai'] = null;
                    $payload['feedback'] = null;
                    if ($file) {
                        if (StoredUpload::exists($existing?->file)) {
                            StoredUpload::delete($existing->file);
                        }
                        $payload['file'] = $file->store('lms/quiz/'.$quiz->quiz_id.'/'.$mahasiswa->mahasiswa_id, 'private');
                    }
                }
                LmsQuizJawaban::updateOrCreate(
                    ['attempt_id' => $attempt->attempt_id, 'soal_id' => $soal->soal_id],
                    $payload
                );
            }

            $attempt->update([
                'status' => $adaEssay ? 'submitted' : 'graded',
                'submitted_at' => now(), 'nilai_pg' => $nilaiPg,
                'nilai_essay' => $adaEssay ? null : 0,
                'nilai_total' => $adaEssay ? null : $nilaiPg,
                'feedback' => null,
                'dinilai_at' => $adaEssay ? null : now(),
                'dinilai_oleh' => null, 'izinkan_ulang' => false,
            ]);
        });

        if ($attempt->fresh()?->status === 'graded') {
            $syncService->sync($quiz->jadwal);
        }

        return redirect()->route('mahasiswa.lms.quiz.show', $quiz)
            ->with('success', 'Jawaban quiz berhasil dikirim dan sekarang terkunci.');
    }

    public function downloadJawaban(LmsQuizJawaban $jawaban)
    {
        $jawaban->load('attempt');
        abort_unless((int) $jawaban->attempt->mahasiswa_id === (int) Auth::guard('mahasiswa')->id(), 403);
        abort_unless(StoredUpload::exists($jawaban->file), 404);

        return StoredUpload::disk($jawaban->file)->download($jawaban->file, basename($jawaban->file));
    }

    private function soalUntukAttempt(LmsQuiz $quiz, LmsQuizAttempt $attempt): Collection
    {
        $soalById = $quiz->soal->keyBy('soal_id');
        $urutanTersimpan = collect($attempt->urutan_soal ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $soalById->has($id))
            ->unique()
            ->values();
        $soalBaru = $quiz->soal->reject(fn ($soal) => $urutanTersimpan->contains((int) $soal->soal_id));
        $urutan = $urutanTersimpan
            ->concat($this->buatUrutanSoal($soalBaru, $quiz, $attempt))
            ->values();

        if ($urutan->all() !== array_map('intval', $attempt->urutan_soal ?? [])) {
            $attempt->update(['urutan_soal' => $urutan->all()]);
        }

        return $urutan->map(fn ($soalId) => $soalById->get($soalId))->filter()->values();
    }

    private function buatUrutanSoal(Collection $soal, LmsQuiz $quiz, LmsQuizAttempt $attempt): array
    {
        return $soal->sortBy(fn ($item) => hash(
            'sha256',
            $quiz->quiz_id.'|'.$attempt->mahasiswa_id.'|'.$item->soal_id
        ))->pluck('soal_id')->map(fn ($id) => (int) $id)->values()->all();
    }

    private function terdaftar(Jadwal $jadwal, $mahasiswa): bool
    {
        $krs = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('kurikulum_id', $jadwal->kurikulum_id)->where('ta_id', $jadwal->ta_id)->exists();
        $kelasMahasiswa = strtolower((string) $mahasiswa->kelas);
        $kelasJadwal = strtolower((string) $jadwal->jenis_kelas);

        return $krs && ($kelasMahasiswa === 'karyawan' ? $kelasJadwal === 'karyawan' : $kelasJadwal === 'reguler');
    }
}
