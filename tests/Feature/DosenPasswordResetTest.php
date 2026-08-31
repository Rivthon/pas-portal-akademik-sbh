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

    public function test_authorized_admin_can_reset_dosen_password_to_a_random_password(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'dosen-reset-password',
            'guard_name' => 'web',
        ]);
        $admin = User::factory()->create();
        $admin->givePermissionTo($permission);
        $dosen = Dosen::query()->firstOrFail();
        $oldPasswordHash = $dosen->password;

        $response = $this->actingAs($admin)
            ->post(route('admin.dosen.reset-password', $dosen));

        $response->assertRedirect(route('admin.dosen.index'))
            ->assertSessionHas('reset_password_result');

        $result = $response->getSession()->get('reset_password_result');

        $this->assertSame($dosen->nama, $result['nama']);
        $this->assertSame(12, strlen($result['password']));
        $this->assertNotSame($oldPasswordHash, $dosen->fresh()->password);
        $this->assertTrue(Hash::check($result['password'], $dosen->fresh()->password));
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
