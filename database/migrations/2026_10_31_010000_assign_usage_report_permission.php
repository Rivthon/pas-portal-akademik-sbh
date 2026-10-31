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
            ->where('name', 'usage-report-list')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'BAAK'])
            ->pluck('id')
            ->each(function ($roleId) use ($permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionId = Permission::query()
            ->where('name', 'usage-report-list')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        $roleIds = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'BAAK'])
            ->pluck('id');

        DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('role_id', $roleIds)
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
