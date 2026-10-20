<?php

namespace Tests\Unit;

use App\Support\TeachingHonorPeriod;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeachingHonorPeriodTest extends TestCase
{
    #[Test]
    public function period_ends_on_the_twentieth_without_overlapping_previous_period(): void
    {
        $period = TeachingHonorPeriod::fromKey('2026-12');

        $this->assertSame('2026-11-21', $period['start']->toDateString());
        $this->assertSame('2026-12-20', $period['end']->toDateString());
        $this->assertSame('2026-12', $period['key']);
    }

    #[Test]
    public function current_period_moves_after_the_cutoff_day(): void
    {
        $this->assertSame(
            '2026-10',
            TeachingHonorPeriod::currentKey(CarbonImmutable::parse('2026-10-20'))
        );
        $this->assertSame(
            '2026-11',
            TeachingHonorPeriod::currentKey(CarbonImmutable::parse('2026-10-21'))
        );
    }
}
