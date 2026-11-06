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
        $bypass = Permission::query()->where([
            'name' => 'website-maintenance-bypass',
            'guard_name' => 'web',
        ])->first();

        if (! $bypass) {
            $bypass = new Permission([
                'name' => 'website-maintenance-bypass',
                'guard_name' => 'web',
            ]);
            $bypass->id = ((int) Permission::query()->max('id')) + 1;
            $bypass->save();
        }

        $bypassId = Permission::query()
            ->where('name', 'website-maintenance-bypass')
            ->where('guard_name', 'web')
            ->value('id');
        $toggleId = Permission::query()
            ->where('name', 'website-maintenance-toggle')
            ->where('guard_name', 'web')
            ->value('id');

        $baakRoleIds = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['BAAK', 'baak'])
            ->pluck('id');

        if ($toggleId) {
            DB::table('role_has_permissions')
                ->where('permission_id', $toggleId)
                ->whereIn('role_id', $baakRoleIds)
                ->delete();
        }

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'BAAK', 'baak'])
            ->pluck('id')
            ->each(fn ($roleId) => DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $bypassId,
                'role_id' => $roleId,
            ]));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $bypassId = Permission::query()
            ->where('name', 'website-maintenance-bypass')
            ->where('guard_name', 'web')
            ->value('id');

        if ($bypassId) {
            DB::table('role_has_permissions')->where('permission_id', $bypassId)->delete();
            Permission::query()->whereKey($bypassId)->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
