<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'lms-list',
        'rps-list',
        'bap-pengajaran-list',
        'krs-archive-list',
        'krs-archive-export',
    ];

    public function up(): void
    {
        $created = collect($this->permissions)->mapWithKeys(function (string $name) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            return [$name => $permission];
        });

        Role::where('guard_name', 'web')
            ->whereIn('name', ['Super Admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($created->values()));

        Role::where('guard_name', 'web')
            ->whereIn('name', ['BAAK', 'baak', 'Admin Akademik'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo(
                $created->only(['lms-list', 'rps-list', 'krs-archive-list', 'krs-archive-export'])->values()
            ));

        Role::where('guard_name', 'web')
            ->whereIn('name', ['UPMI', 'upmi', 'Unit Penjamin Mutu Internal'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo(
                $created->only(['lms-list', 'rps-list'])->values()
            ));

        Role::where('guard_name', 'web')
            ->whereIn('name', ['BAUK', 'bauk', 'Admin Keuangan'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($created['bap-pengajaran-list']));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('guard_name', 'web')
            ->whereIn('name', $this->permissions)
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
