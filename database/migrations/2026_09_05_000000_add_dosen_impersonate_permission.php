<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'dosen-impersonate',
            'guard_name' => 'web',
        ]);

        Role::where('name', 'Super Admin')
            ->where('guard_name', 'web')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'dosen-impersonate')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            Role::where('guard_name', 'web')
                ->get()
                ->each(fn (Role $role) => $role->revokePermissionTo($permission));

            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
