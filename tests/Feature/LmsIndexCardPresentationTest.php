<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
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

    public function test_dosen_who_teaches_a_meeting_can_access_both_regular_and_employee_lms_classes(): void
    {
        $dosen = Dosen::findOrFail(15);
        $regular = Jadwal::findOrFail(1669);
        $karyawan = Jadwal::findOrFail(1778);

        $this->actingAs($dosen, 'dosen')->get(route('dosen.lms.index'))
            ->assertOk()
            ->assertSee(route('dosen.lms.kelola', $regular), false)
            ->assertSee(route('dosen.lms.kelola', $karyawan), false)
            ->assertSee('Reguler A')
            ->assertSee('Reguler B');

        $this->actingAs($dosen, 'dosen')->get(route('dosen.lms.kelola', $karyawan))->assertOk();
    }
}
