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

    public function test_old_meeting_does_not_grant_access_to_an_unassigned_class(): void
    {
        $dosen = Dosen::findOrFail(15);
        $regular = Jadwal::findOrFail(1879);
        $karyawan = Jadwal::findOrFail(1919);
        $oldUnassignedClassWithMeeting = Jadwal::findOrFail(1778);

        $this->actingAs($dosen, 'dosen')->get(route('dosen.lms.index'))
            ->assertOk()
            ->assertSee(route('dosen.lms.kelola', $regular), false)
            ->assertDontSee(route('dosen.lms.kelola', $karyawan), false);

        $this->actingAs($dosen, 'dosen')->get(route('dosen.lms.kelola', $karyawan))->assertNotFound();
        $this->actingAs($dosen, 'dosen')->get(route('dosen.lms.kelola', $oldUnassignedClassWithMeeting))->assertNotFound();
    }
}
