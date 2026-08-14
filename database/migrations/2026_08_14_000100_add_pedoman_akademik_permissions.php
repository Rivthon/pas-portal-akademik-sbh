<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'pedoman-akademik-list',
        'pedoman-akademik-create',
        'pedoman-akademik-edit',
        'pedoman-akademik-delete',
    ];

    public function up(): void
    {
        $permissions = collect($this->permissions)->mapWithKeys(function (string $name) {
            return [$name => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ])];
        });

        Role::where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'Admin', 'BAAK', 'baak', 'Admin Akademik'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions->values()));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('guard_name', 'web')
            ->whereIn('name', $this->permissions)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
