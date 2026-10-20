<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::where([
            'name' => 'bap-pengajaran-pay',
            'guard_name' => 'web',
        ])->first();

        if (! $permission) {
            $permission = new Permission([
                'name' => 'bap-pengajaran-pay',
                'guard_name' => 'web',
            ]);
            // Sebagian dump database lama kehilangan AUTO_INCREMENT pada tabel permissions.
            // ID eksplisit aman pada instalasi normal maupun database hasil impor tersebut.
            $permission->id = ((int) Permission::max('id')) + 1;
            $permission->save();
        }

        Role::where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'BAUK', 'bauk'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('guard_name', 'web')
            ->where('name', 'bap-pengajaran-pay')
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
