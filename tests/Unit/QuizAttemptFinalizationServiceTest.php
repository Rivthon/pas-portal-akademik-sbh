<?php

namespace Tests\Unit;

use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Services\QuizAttemptFinalizationService;
use Carbon\Carbon;
use Tests\TestCase;

class QuizAttemptFinalizationServiceTest extends TestCase
{
    public function test_earliest_of_deadline_and_duration_becomes_expiration_time(): void
    {
        Carbon::setTestNow('2026-10-12 08:00:00');
        $quiz = new LmsQuiz([
            'deadline' => now()->addHour(),
            'durasi_menit' => 30,
        ]);
        $attempt = new LmsQuizAttempt([
            'status' => 'draft',
            'started_at' => now(),
            'izinkan_ulang' => false,
        ]);
        $attempt->setRelation('quiz', $quiz);

        $expiresAt = (new QuizAttemptFinalizationService)->expiresAt($attempt);

        $this->assertSame('2026-10-12 08:30:00', $expiresAt?->format('Y-m-d H:i:s'));
        Carbon::setTestNow();
    }

    public function test_retry_ignores_old_deadline_but_keeps_new_duration_limit(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $quiz = new LmsQuiz([
            'deadline' => now()->subHour(),
            'durasi_menit' => 20,
        ]);
        $attempt = new LmsQuizAttempt([
            'status' => 'graded',
            'started_at' => now(),
            'izinkan_ulang' => true,
        ]);
        $attempt->setRelation('quiz', $quiz);

        $service = new QuizAttemptFinalizationService;

        $this->assertSame('2026-10-12 09:20:00', $service->expiresAt($attempt)?->format('Y-m-d H:i:s'));
        $this->assertTrue($service->canBeAutomaticallyFinalized($attempt));
        Carbon::setTestNow();
    }
}
