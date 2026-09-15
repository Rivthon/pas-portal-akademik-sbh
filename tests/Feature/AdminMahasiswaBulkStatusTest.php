<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminMahasiswaBulkStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bulk_status_controls_are_visible_to_authorized_admin(): void
    {
        $this->actingAs($this->authorizedAdmin())
            ->get(route('admin.mahasiswa.index'))
            ->assertOk()
            ->assertSee('Awalan NIM')
            ->assertSee('Terapkan Status Massal')
            ->assertSee('Semua hasil filter (seluruh halaman)');
    }

    public function test_admin_can_mark_all_students_matching_nim_prefix_as_graduated(): void
    {
        $admin = $this->authorizedAdmin();
        $targets = Mahasiswa::query()->orderBy('mahasiswa_id')->limit(3)->get();
        $outside = Mahasiswa::query()->whereNotIn('mahasiswa_id', $targets->pluck('mahasiswa_id'))->firstOrFail();
        $outsideStatus = $outside->status_mhs;

        foreach ($targets as $index => $mahasiswa) {
            $mahasiswa->update([
                'nim' => '9915'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                'status_mhs' => 'aktif',
            ]);
        }

        $this->actingAs($admin)->postJson(route('admin.mahasiswa.bulkUpdateStatus'), [
            'scope' => 'filtered',
            'status_mhs' => 'lulus',
            'nim_prefix' => '9915',
        ])->assertOk()
            ->assertJsonPath('target_count', 3)
            ->assertJsonPath('updated', 3);

        $this->assertSame(3, Mahasiswa::query()
            ->where('nim', 'like', '9915%')
            ->where('status_mhs', 'lulus')
            ->count());
        $this->assertSame($outsideStatus, $outside->fresh()->status_mhs);
    }

    public function test_all_results_scope_is_rejected_without_a_filter(): void
    {
        $this->actingAs($this->authorizedAdmin())
            ->postJson(route('admin.mahasiswa.bulkUpdateStatus'), [
                'scope' => 'filtered',
                'status_mhs' => 'lulus',
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Gunakan minimal satu filter sebelum menerapkan status ke seluruh hasil pencarian.'
            );
    }

    public function test_user_without_edit_permission_cannot_bulk_update_status(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.mahasiswa.bulkUpdateStatus'), [
                'scope' => 'filtered',
                'status_mhs' => 'lulus',
                'nim_prefix' => '9915',
            ])
            ->assertForbidden();
    }

    private function authorizedAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('mahasiswa-edit', 'web'));

        return $admin;
    }
}
