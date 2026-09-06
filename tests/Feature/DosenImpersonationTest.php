<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DosenImpersonationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_with_permission_can_login_as_dosen_and_return_to_admin(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findByName('dosen-impersonate', 'web'));
        $dosen = Dosen::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.dosen.impersonate', $dosen))
            ->assertRedirect(route('dosen.dashboard'))
            ->assertSessionHas('impersonating_dosen', true)
            ->assertSessionHas('impersonator_admin_id', $admin->id)
            ->assertSessionHas('impersonated_dosen_id', $dosen->dosen_id);

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertTrue(Auth::guard('dosen')->check());
        $this->assertSame((string) $dosen->dosen_id, (string) Auth::guard('dosen')->id());

        $this->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee('Mode Login Sebagai Dosen')
            ->assertSee($dosen->nama);

        $this->post(route('admin.dosen.impersonate.stop'))
            ->assertRedirect(route('admin.dosen.index'))
            ->assertSessionMissing('impersonating_dosen')
            ->assertSessionMissing('impersonated_dosen_id');

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('dosen')->check());
    }

    public function test_admin_without_permission_cannot_login_as_dosen(): void
    {
        $admin = User::factory()->create();
        $dosen = Dosen::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.dosen.impersonate', $dosen))
            ->assertForbidden();

        $this->assertFalse(Auth::guard('dosen')->check());
    }

    public function test_dosen_logout_returns_impersonating_admin_without_destroying_admin_session(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findByName('dosen-impersonate', 'web'));
        $dosen = Dosen::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.dosen.impersonate', $dosen));

        $this->post(route('dosen.logout'))
            ->assertRedirect(route('admin.dosen.index'))
            ->assertSessionMissing('impersonating_dosen');

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('dosen')->check());
    }

    public function test_admin_can_switch_from_mahasiswa_impersonation_to_dosen_without_conflict(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo([
            Permission::findByName('mahasiswa-impersonate', 'web'),
            Permission::findByName('dosen-impersonate', 'web'),
        ]);
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $dosen = Dosen::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.mahasiswa.impersonate', $mahasiswa))
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHas('impersonating_mahasiswa', true);

        $this->post(route('admin.dosen.impersonate', $dosen))
            ->assertRedirect(route('dosen.dashboard'))
            ->assertSessionMissing('impersonating_mahasiswa')
            ->assertSessionMissing('impersonated_mahasiswa_id')
            ->assertSessionHas('impersonating_dosen', true)
            ->assertSessionHas('impersonated_dosen_id', $dosen->dosen_id);

        $this->assertFalse(Auth::guard('mahasiswa')->check());
        $this->assertTrue(Auth::guard('dosen')->check());
        $this->assertTrue(Auth::guard('web')->check());
    }
}
