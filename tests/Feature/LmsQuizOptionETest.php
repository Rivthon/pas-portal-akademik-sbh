<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\LmsQuiz;
use App\Models\LmsQuizSoal;
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
}
