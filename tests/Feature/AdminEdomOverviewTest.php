<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminEdomOverviewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_overview_uses_all_years_and_consistent_completion_totals(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('evaluasi-list', 'web'));
        $response = $this->actingAs($admin)->get(route('admin.penilaian.overview'));
        $response->assertOk()->assertSee('Ringkasan EDOM')->assertSee('Persentase Terisi');

        foreach ($response->viewData('dosenRows') as $dosen) {
            $expected = DB::table('penilaian')->where('dosen_id', $dosen->dosen_id)->avg('nilai');
            $this->assertEquals($expected === null ? null : round($expected, 2), $dosen->rata_rata_edom);
        }

        $statistics = $response->viewData('statistics');
        $this->assertSame($statistics['total'], $statistics['filled'] + $statistics['unfilled']);
        $this->assertSame($statistics['total'], $statistics['years']->sum('total'));
        foreach ($statistics['years'] as $year) {
            $this->assertGreaterThanOrEqual(0, $year['unfilled']);
            $this->assertSame($year['total'], $year['filled'] + $year['unfilled']);
            if ($year['total'] > 0) {
                $this->assertEquals(round($year['filled'] / $year['total'] * 100, 1), $year['percent']);
            }
        }
    }

    public function test_overview_requires_evaluasi_list_permission(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.penilaian.overview'))->assertForbidden();
    }
}
