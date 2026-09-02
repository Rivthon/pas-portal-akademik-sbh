<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DosenPasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reset_password_prioritizes_nidn(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'dosen-reset-password',
            'guard_name' => 'web',
        ]);
        $admin = User::factory()->create();
        $admin->givePermissionTo($permission);
        $dosen = Dosen::query()->firstOrFail();
        $dosen->forceFill([
            'nidn' => '0412345678',
            'kd_dosen' => 'DTF04336502001',
        ])->save();
        $oldPasswordHash = $dosen->password;

        $response = $this->actingAs($admin)
            ->post(route('admin.dosen.reset-password', $dosen));

        $response->assertRedirect(route('admin.dosen.index'))
            ->assertSessionHas('reset_password_result');

        $result = $response->getSession()->get('reset_password_result');

        $this->assertSame($dosen->nama, $result['nama']);
        $this->assertSame('0412345678', $result['password']);
        $this->assertSame('NIDN', $result['source']);
        $this->assertNotSame($oldPasswordHash, $dosen->fresh()->password);
        $this->assertTrue(Hash::check($result['password'], $dosen->fresh()->password));
    }

    public function test_reset_password_uses_dosen_code_when_nidn_is_unavailable(): void
    {
        $permission = Permission::findOrCreate('dosen-reset-password', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo($permission);
        $dosen = Dosen::query()->firstOrFail();
        $dosen->forceFill([
            'nidn' => 'null',
            'kd_dosen' => 'DTG04336503009',
        ])->save();

        $response = $this->actingAs($admin)
            ->post(route('admin.dosen.reset-password', $dosen));

        $result = $response->getSession()->get('reset_password_result');

        $this->assertSame('DTG04336503009', $result['password']);
        $this->assertSame('kode dosen', $result['source']);
        $this->assertTrue(Hash::check('DTG04336503009', $dosen->fresh()->password));
    }

    public function test_admin_without_permission_cannot_reset_dosen_password(): void
    {
        $admin = User::factory()->create();
        $dosen = Dosen::query()->firstOrFail();
        $oldPasswordHash = $dosen->password;

        $this->actingAs($admin)
            ->post(route('admin.dosen.reset-password', $dosen))
            ->assertForbidden();

        $this->assertSame($oldPasswordHash, $dosen->fresh()->password);
    }
}
