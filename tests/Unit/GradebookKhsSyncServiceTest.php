<?php

namespace Tests\Unit;

use App\Services\GradebookKhsSyncService;
use PHPUnit\Framework\TestCase;

class GradebookKhsSyncServiceTest extends TestCase
{
    public function test_gradebook_is_normalized_before_khs_weight_is_applied(): void
    {
        $service = new GradebookKhsSyncService;

        $nilaiTugas = $service->normalizedScore(160, 200);
        $nilaiAkhir = $service->finalScore(
            ['tugas' => $nilaiTugas],
            ['tugas' => 25]
        );

        $this->assertSame(80.0, $nilaiTugas);
        $this->assertSame(20.0, $nilaiAkhir);
    }

    public function test_normalized_gradebook_score_is_limited_to_zero_and_one_hundred(): void
    {
        $service = new GradebookKhsSyncService;

        $this->assertSame(100.0, $service->normalizedScore(120, 100));
        $this->assertSame(0.0, $service->normalizedScore(-10, 100));
        $this->assertSame(0.0, $service->normalizedScore(10, 0));
    }

    public function test_letter_grade_matches_existing_khs_thresholds(): void
    {
        $service = new GradebookKhsSyncService;

        $this->assertSame('A', $service->letterGrade(85.5));
        $this->assertSame('AB', $service->letterGrade(78.5));
        $this->assertSame('E', $service->letterGrade(45.49));
    }
}
