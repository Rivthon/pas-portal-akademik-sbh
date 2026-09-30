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
}
