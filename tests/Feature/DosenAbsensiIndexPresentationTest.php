<?php

namespace Tests\Feature;

use App\Models\Dosen;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenAbsensiIndexPresentationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_absensi_cards_are_grouped_in_semester_collapses_with_complete_information(): void
    {
        $dosen = Dosen::findOrFail(22);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi.index'))
            ->assertOk()
            ->assertSee('absensiContainer', false);

        $response = $this->actingAs($dosen, 'dosen')
            ->getJson(route('dosen.absensi.filter', [
                'jenis_kelas' => 'semua',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'html',
                'statistik' => ['total', 'reguler', 'karyawan'],
            ]);

        $this->assertGreaterThan(0, $response->json('statistik.total'));

        $html = $response->json('html');
        $this->assertStringContainsString('semesterAbsensiAccordion', $html);
        $this->assertStringContainsString('accordion-collapse collapse show', $html);
        $this->assertStringContainsString('Progres Pertemuan', $html);
        $this->assertStringContainsString('Ruang / SKS', $html);
        $this->assertStringContainsString('Dosen Pengampu', $html);
        $this->assertStringContainsString('Kelola Pertemuan & Absensi', $html);
        $this->assertStringContainsString('data-jadwal-id=', $html);
    }
}
