<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizSoal;
use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LmsQuizOptionETest extends TestCase
{
    use DatabaseTransactions;

    public function test_lecturer_can_save_option_e_as_quiz_answer_key(): void
    {
        $quiz = LmsQuiz::with('jadwal')->firstOrFail();
        $quiz->attempts()->delete();
        $dosen = Dosen::findOrFail($quiz->dosen_id);
        $question = 'Soal dengan opsi E '.uniqid();

        $this->actingAs($dosen, 'dosen')->post(route('dosen.lms.quiz.soal.store', $quiz), [
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => $question,
            'opsi' => ['Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E'],
            'kunci_jawaban' => '4',
            'bobot' => 10,
        ])->assertSessionHas('success');

        $saved = LmsQuizSoal::where('quiz_id', $quiz->quiz_id)->where('pertanyaan', $question)->firstOrFail();
        $this->assertCount(5, $saved->opsi);
        $this->assertSame('Pilihan E', $saved->opsi[4]);
        $this->assertSame('4', $saved->kunci_jawaban);
    }

    public function test_editing_question_after_quiz_started_returns_friendly_message(): void
    {
        $quiz = LmsQuiz::with(['jadwal', 'soal'])->whereHas('soal')->firstOrFail();
        $dosen = Dosen::findOrFail($quiz->dosen_id);
        $soal = $quiz->soal->first();
        $originalQuestion = $soal->pertanyaan;

        if (! $quiz->attempts()->exists()) {
            $mahasiswa = Mahasiswa::query()->firstOrFail();
            LmsQuizAttempt::create([
                'quiz_id' => $quiz->quiz_id,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'status' => 'draft',
                'started_at' => now(),
            ]);
        }

        $this->actingAs($dosen, 'dosen')
            ->put(route('dosen.lms.quiz.soal.update', $soal), [
                'tipe' => $soal->tipe,
                'pertanyaan' => 'Pertanyaan tidak boleh tersimpan',
                'bobot' => $soal->bobot,
            ])
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Soal tidak dapat diubah karena quiz sudah mulai dikerjakan mahasiswa.'
            );

        $this->assertSame($originalQuestion, $soal->fresh()->pertanyaan);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.lms.quiz.manage', $quiz))
            ->assertOk()
            ->assertSee('Soal quiz sudah dikunci.');
    }

    public function test_lecturer_can_copy_all_questions_to_another_quiz(): void
    {
        $source = LmsQuiz::with(['jadwal', 'soal'])->whereHas('soal')->firstOrFail();
        $dosen = Dosen::findOrFail($source->dosen_id);
        if (! $source->attempts()->exists()) {
            $mahasiswa = Mahasiswa::query()->firstOrFail();
            LmsQuizAttempt::create([
                'quiz_id' => $source->quiz_id,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'status' => 'draft',
                'started_at' => now(),
            ]);
        }
        $destination = LmsQuiz::create([
            'jadwal_id' => $source->jadwal_id,
            'pertemuan_id' => null,
            'dosen_id' => $source->dosen_id,
            'judul' => 'Quiz tujuan salinan '.uniqid(),
            'deskripsi' => 'Quiz tujuan pengujian',
            'mulai_at' => null,
            'deadline' => null,
            'durasi_menit' => 60,
            'aktif' => false,
        ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.lms.quiz.soal.copy', $source), [
                'destination_quiz_id' => $destination->quiz_id,
            ])
            ->assertRedirect(route('dosen.lms.quiz.manage', $destination))
            ->assertSessionHas('success');

        $copiedQuestions = $destination->soal()->orderBy('urutan')->get();
        $this->assertCount($source->soal->count(), $copiedQuestions);

        foreach ($source->soal->values() as $index => $sourceQuestion) {
            $copied = $copiedQuestions[$index];
            $this->assertSame($sourceQuestion->tipe, $copied->tipe);
            $this->assertSame($sourceQuestion->pertanyaan, $copied->pertanyaan);
            $this->assertSame($sourceQuestion->opsi, $copied->opsi);
            $this->assertSame($sourceQuestion->kunci_jawaban, $copied->kunci_jawaban);
            $this->assertSame((float) $sourceQuestion->bobot, (float) $copied->bobot);
        }
    }

    public function test_quiz_cannot_copy_questions_from_itself(): void
    {
        $quiz = LmsQuiz::with('soal')->whereHas('soal')->firstOrFail();
        $quiz->attempts()->delete();
        $dosen = Dosen::findOrFail($quiz->dosen_id);
        $questionCount = $quiz->soal->count();

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.lms.quiz.soal.copy', $quiz), [
                'destination_quiz_id' => $quiz->quiz_id,
            ])
            ->assertSessionHasErrors('destination_quiz_id');

        $this->assertSame($questionCount, $quiz->soal()->count());
    }

    public function test_new_quiz_is_saved_as_draft_when_active_checkbox_is_not_checked(): void
    {
        $existingQuiz = LmsQuiz::with('jadwal')->firstOrFail();
        $dosen = Dosen::findOrFail($existingQuiz->dosen_id);
        $title = 'Quiz draft '.uniqid();

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.lms.quiz.store', $existingQuiz->jadwal), [
                'judul' => $title,
                'deskripsi' => 'Quiz harus tersimpan sebagai draft',
                'durasi_menit' => 60,
            ])
            ->assertRedirect();

        $quiz = LmsQuiz::query()->where('judul', $title)->firstOrFail();
        $this->assertFalse($quiz->aktif);
    }
}
