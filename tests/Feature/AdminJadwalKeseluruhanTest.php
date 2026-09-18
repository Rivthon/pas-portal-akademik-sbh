<?php

namespace Tests\Feature;

use App\Models\ProgramStudi;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminJadwalKeseluruhanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_admin_can_view_and_filter_complete_schedule(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('jadwal-list', 'web'));
        $prodi = ProgramStudi::query()->firstOrFail();
        $ruangan = Ruangan::query()->first();

        $this->actingAs($admin)
            ->get(route('admin.jadwal-keseluruhan.index'))
            ->assertOk()
            ->assertSee('Jadwal Perkuliahan Keseluruhan')
            ->assertSee('Senin')
            ->assertSee('Minggu');

        $response = $this->get(route('admin.jadwal-keseluruhan.index', array_filter([
            'jurusan_id' => $prodi->jurusan_id,
            'semester' => 1,
            'kelas' => 'reguler',
            'ruangan_id' => $ruangan?->ruangan_id,
        ])));

        $response->assertOk()
            ->assertSee('Jadwal Perkuliahan Keseluruhan')
            ->assertSee('Program Studi')
            ->assertSee('Semester')
            ->assertSee('Kelas')
            ->assertSee('Ruangan');
    }

    public function test_user_without_schedule_permission_cannot_view_complete_schedule(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.jadwal-keseluruhan.index'))
            ->assertForbidden();
    }
}
