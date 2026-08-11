<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\LmsCalendarNote;
use App\Models\LmsTugas;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\LmsCalendarService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LmsCalendarTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_can_create_a_private_calendar_note(): void
    {
        $dosen = Dosen::query()->firstOrFail();

        $this->actingAs($dosen, 'dosen')
            ->from(route('dosen.lms.index'))
            ->post(route('dosen.lms.calendar.notes.store'), [
                'title' => 'Catatan dosen',
                'description' => 'Hanya untuk dosen ini',
                'note_date' => '2026-07-30',
                'note_time' => '09:00',
                'color' => 'success',
            ])
            ->assertRedirect(route('dosen.lms.index'));

        $this->assertDatabaseHas('lms_calendar_notes', [
            'owner_type' => 'dosen',
            'owner_id' => $dosen->dosen_id,
            'title' => 'Catatan dosen',
        ]);
    }

    public function test_private_notes_are_isolated_by_owner_type_and_id(): void
    {
        $dosen = Dosen::query()->firstOrFail();
        $mahasiswa = Mahasiswa::query()->firstOrFail();

        LmsCalendarNote::create([
            'owner_type' => 'dosen',
            'owner_id' => $dosen->dosen_id,
            'title' => 'Milik dosen',
            'note_date' => '2026-07-30',
            'color' => 'success',
        ]);
        LmsCalendarNote::create([
            'owner_type' => 'mahasiswa',
            'owner_id' => $mahasiswa->mahasiswa_id,
            'title' => 'Milik mahasiswa',
            'note_date' => '2026-07-30',
            'color' => 'primary',
        ]);

        $service = app(LmsCalendarService::class);
        $dosenEvents = collect($service->events(collect(), 'dosen', 'dosen', (int) $dosen->dosen_id));
        $mahasiswaEvents = collect($service->events(collect(), 'mahasiswa', 'mahasiswa', (int) $mahasiswa->mahasiswa_id));

        $this->assertTrue($dosenEvents->contains('title', 'Milik dosen'));
        $this->assertFalse($dosenEvents->contains('title', 'Milik mahasiswa'));
        $this->assertTrue($mahasiswaEvents->contains('title', 'Milik mahasiswa'));
        $this->assertFalse($mahasiswaEvents->contains('title', 'Milik dosen'));
    }

    public function test_a_user_cannot_delete_another_users_note(): void
    {
        $dosen = Dosen::query()->firstOrFail();
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $note = LmsCalendarNote::create([
            'owner_type' => 'dosen',
            'owner_id' => $dosen->dosen_id,
            'title' => 'Milik dosen',
            'note_date' => '2026-07-30',
            'color' => 'success',
        ]);

        Auth::guard('dosen')->logout();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->delete(route('mahasiswa.lms.calendar.notes.destroy', $note))
            ->assertForbidden();

        $this->assertDatabaseHas('lms_calendar_notes', ['note_id' => $note->note_id]);
    }

    public function test_calendar_is_rendered_on_each_lms_index(): void
    {
        $dosen = Dosen::query()->firstOrFail();
        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.lms.index'))
            ->assertOk()
            ->assertSee('dosenLmsCalendar', false);

        Auth::guard('dosen')->logout();
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.lms.index'))
            ->assertOk()
            ->assertSee('mahasiswaLmsCalendar', false);

        Auth::guard('mahasiswa')->logout();
        $admin = User::query()->firstOrFail();
        $this->actingAs($admin)
            ->get(route('admin.lms.index'))
            ->assertOk()
            ->assertSee('adminLmsCalendar', false);
    }

    public function test_active_task_deadline_is_rendered_for_enrolled_mahasiswa(): void
    {
        $tugas = LmsTugas::findOrFail(7);
        $mahasiswa = Mahasiswa::findOrFail(248);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.lms.index'))
            ->assertOk()
            ->assertSee('tugas-'.$tugas->tugas_id, false)
            ->assertSee('Deadline tugas: '.$tugas->judul, false)
            ->assertSee($tugas->deadline->toDateString(), false)
            ->assertSee('const focusUpcoming = true', false);
    }
}
