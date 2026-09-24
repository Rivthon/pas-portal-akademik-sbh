<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(['cuti-list', 'cuti-validasi'])->mapWithKeys(function (string $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            return [$name => $permission];
        });

        Role::where('guard_name', 'web')
            ->whereIn('name', [
                'Super Admin', 'Admin', 'BAAK', 'baak', 'Admin Akademik',
                'Admin Kemahasiswaan', 'Kemahasiswaan',
            ])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions->values()));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('guard_name', 'web')->whereIn('name', ['cuti-list', 'cuti-validasi'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
