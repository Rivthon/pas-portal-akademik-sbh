<?php

namespace Tests\Feature;

use App\Models\CalendarAkademik;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CalendarAkademikFileAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_baak_can_open_calendar_file_through_application_endpoint(): void
    {
        $calendar = CalendarAkademik::findOrFail(1);
        $user = User::query()->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('admin.calender.file', $calendar));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('inline;', (string) $response->headers->get('content-disposition'));
    }

    public function test_dosen_from_same_program_can_open_active_calendar_file(): void
    {
        $calendar = CalendarAkademik::findOrFail(1);
        $dosen = Dosen::where('jurusan_id', $calendar->jurusan_id)->firstOrFail();

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.calendar-akademik.file', $calendar))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_mahasiswa_from_same_program_can_open_active_calendar_file(): void
    {
        $calendar = CalendarAkademik::findOrFail(1);
        $mahasiswa = Mahasiswa::where('jurusan_id', $calendar->jurusan_id)->firstOrFail();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.calendar-akademik.file', $calendar))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_mahasiswa_from_another_program_cannot_open_calendar_file(): void
    {
        $calendar = CalendarAkademik::findOrFail(1);
        $mahasiswa = Mahasiswa::where('jurusan_id', '!=', $calendar->jurusan_id)->firstOrFail();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.calendar-akademik.file', $calendar))
            ->assertForbidden();
    }
}
