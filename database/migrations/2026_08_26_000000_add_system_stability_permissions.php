<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'system-health-list',
        'system-error-log-list',
        'system-backup-list',
        'system-backup-create',
        'system-backup-download',
    ];

    public function up(): void
    {
        $permissions = collect($this->permissions)->map(function (string $name) {
            return Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });

        Role::where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'super admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

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
