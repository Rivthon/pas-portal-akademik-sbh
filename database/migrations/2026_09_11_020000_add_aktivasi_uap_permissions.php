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

        $permissionSources = [
            'aktivasi-uap-list' => 'aktivasi-list',
            'aktivasi-uap-update' => 'aktivasi-update',
            'aktivasi-uap-bulk-update' => 'aktivasi-bulk-update',
        ];

        foreach ($permissionSources as $permissionName => $sourcePermissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            Role::query()
                ->whereHas('permissions', fn ($query) => $query->where('name', $sourcePermissionName))
                ->each(fn (Role $role) => $role->givePermissionTo($permission));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'aktivasi-uap-list',
                'aktivasi-uap-update',
                'aktivasi-uap-bulk-update',
            ])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
