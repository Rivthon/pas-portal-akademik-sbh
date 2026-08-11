<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LmsIndexCardPresentationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_lms_cards_show_complete_course_information_grouped_by_semester(): void
    {
        $response = $this->actingAs(Dosen::findOrFail(22), 'dosen')
            ->get(route('dosen.lms.index'));

        $response->assertOk()
            ->assertSee('dosenLmsSemesterAccordion', false)
            ->assertSee('Mata Kuliah')
            ->assertSee('Ruang / SKS')
            ->assertSee('Dosen Pengampu')
            ->assertSee('Progres Pertemuan')
            ->assertSee('Kelola')
            ->assertSee('Gradebook');
    }

    public function test_mahasiswa_lms_cards_show_complete_course_information_grouped_by_semester(): void
    {
        $response = $this->actingAs(Mahasiswa::findOrFail(446), 'mahasiswa')
            ->get(route('mahasiswa.lms.index'));

        $response->assertOk()
            ->assertSee('mahasiswaLmsSemesterAccordion', false)
            ->assertSee('Mata Kuliah')
            ->assertSee('Ruang / SKS')
            ->assertSee('Dosen Pengampu')
            ->assertSee('Progres Pertemuan')
            ->assertSee('Buka Kelas')
            ->assertSee('Quiz');
    }
}
