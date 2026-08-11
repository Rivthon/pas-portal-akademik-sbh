<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRolePermissionCatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_new_admin_features_are_available_in_role_permission_page(): void
    {
        $permissionNames = [
            'lms-list',
            'rps-list',
            'bap-pengajaran-list',
            'krs-archive-list',
            'krs-archive-export',
            'mahasiswa-impersonate',
        ];

        foreach ($permissionNames as $permissionName) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findByName('role-edit'));
        $role = Role::create([
            'name' => 'Role Audit '.uniqid(),
            'guard_name' => 'web',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.roles.edit', $role))
            ->assertOk()
            ->assertSee('Hak Akses Fitur')
            ->assertSee('LMS, RPS &amp; Perkuliahan', false);

        foreach ($permissionNames as $permissionName) {
            $response->assertSee($permissionName);
        }
        foreach (Permission::where('guard_name', 'web')->pluck('name') as $permissionName) {
            $response->assertSee($permissionName);
        }

        $lmsPermission = Permission::findByName('lms-list');
        $this->put(route('admin.roles.update', $role), [
            'name' => $role->name,
            'permission' => [$lmsPermission->id],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertTrue($role->fresh()->hasPermissionTo('lms-list'));
        $this->assertCount(1, $role->fresh()->permissions);
    }

    public function test_feature_routes_use_their_matching_permissions(): void
    {
        $expectedMiddleware = [
            'admin.lms.index' => 'permission:lms-list',
            'admin.rps.index' => 'permission:rps-list',
            'admin.bap-pengajaran.index' => 'permission:bap-pengajaran-list',
            'admin.bap-pengajaran.pdf' => 'permission:bap-pengajaran-list',
            'admin.krs-archive.index' => 'permission:krs-archive-list',
            'admin.krs-archive.download-semester' => 'permission:krs-archive-export',
            'admin.settings.edit' => 'permission:settings-edit',
            'admin.aktivasi.index' => 'permission:aktivasi-list',
            'admin.aktivasi-mhs.updateStatus' => 'permission:aktivasi-update',
            'admin.aktivasi-mhs.bulkUpdate' => 'permission:aktivasi-bulk-update',
            'admin.reset.all.status' => 'permission:aktivasi-reset',
            'admin.uap.index' => 'permission:list-uap',
            'admin.uap.simpanNilai' => 'permission:input-uap',
            'admin.nilai.export' => 'permission:nilai-export',
            'admin.tahun-ajaran.updateStatus' => 'permission:tahun-ajaran-status',
            'admin.resetPassword' => 'permission:mahasiswa-reset-password',
            'admin.simpanPembayaran' => 'permission:pembayaran-update',
            'admin.skpi.sertifikasi' => 'permission:skpi-sertifikasi-list',
            'admin.sertifikasi.catatan' => 'permission:skpi-sertifikasi-edit',
        ];

        foreach ($expectedMiddleware as $routeName => $middleware) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route {$routeName} tidak ditemukan.");
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }

    public function test_user_without_feature_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.lms.index'))->assertForbidden();
        $this->get(route('admin.rps.index'))->assertForbidden();
        $this->get(route('admin.bap-pengajaran.index'))->assertForbidden();
        $this->get(route('admin.krs-archive.index'))->assertForbidden();
        $this->get(route('admin.absensi.index'))->assertForbidden();
    }

    public function test_legacy_lowercase_roles_receive_expected_default_access(): void
    {
        $this->assertTrue(Role::findByName('baak')->hasPermissionTo('lms-list'));
        $this->assertTrue(Role::findByName('baak')->hasPermissionTo('krs-archive-list'));
        $this->assertTrue(Role::findByName('bauk')->hasPermissionTo('bap-pengajaran-list'));
        $this->assertTrue(Role::findByName('upmi')->hasPermissionTo('rps-list'));
    }
}
