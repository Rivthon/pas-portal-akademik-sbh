<?php

namespace Tests\Feature;

use App\Models\Dosen;
use Tests\TestCase;

class DosenDashboardLmsActivityPresentationTest extends TestCase
{
    public function test_lms_activity_is_collapsed_and_limited_to_four_items(): void
    {
        $dosen = Dosen::firstOrFail();

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas LMS')
            ->assertSee('Buka LMS')
            ->assertSee('class="collapse"', false)
            ->assertSee('id="dosenLmsAnnouncements"', false);

        preg_match_all('/class="lms-announcement-item[^"]*"/', $response->getContent(), $matches);

        $this->assertLessThanOrEqual(4, count($matches[0]));
    }
}
