<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissionId = Permission::query()
            ->where('name', 'website-maintenance-toggle')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        // Role Admin lama tidak lagi menjadi bypass. Akses hanya Super Admin dan BAAK.
        $adminRoleIds = Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'Admin')
            ->pluck('id');
        DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('role_id', $adminRoleIds)
            ->delete();

        $baakRoleIds = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['BAAK', 'baak'])
            ->pluck('id');
        DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('role_id', $baakRoleIds)
            ->delete();

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'Super Admin')
            ->pluck('id')
            ->each(fn ($roleId) => DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionId = Permission::query()
            ->where('name', 'website-maintenance-toggle')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        $baakRoleIds = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['BAAK', 'baak'])
            ->pluck('id');
        DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('role_id', $baakRoleIds)
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
