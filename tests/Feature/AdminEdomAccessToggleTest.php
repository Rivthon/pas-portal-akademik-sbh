<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminEdomAccessToggleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_disable_and_enable_student_edom_access(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('penilaian-reset-edom', 'web'));
        $setting = Setting::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.penilaian.toggle-edom'), [
            'enabled' => 0,
        ])->assertRedirect();

        $this->assertFalse($setting->fresh()->edom_enabled);

        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.edom.index'))
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHas('warning', 'Pengisian EDOM tahun akademik aktif belum dibuka oleh admin. Silakan coba kembali sesuai jadwal akademik.');

        $this->actingAs($admin)->post(route('admin.penilaian.toggle-edom'), [
            'enabled' => 1,
        ])->assertRedirect();

        $this->assertTrue($setting->fresh()->edom_enabled);
    }

    public function test_user_without_management_permission_cannot_toggle_edom(): void
    {
        $setting = Setting::query()->firstOrFail();
        $original = $setting->edom_enabled;

        $this->actingAs(User::factory()->create())
            ->post(route('admin.penilaian.toggle-edom'), ['enabled' => ! $original])
            ->assertForbidden();

        $this->assertSame($original, $setting->fresh()->edom_enabled);
    }
}
