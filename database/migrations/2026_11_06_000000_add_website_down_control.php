<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('settings', 'website_down')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('website_down')->default(false)->after('edom_enabled');
            });
        }

        $permission = Permission::query()->where([
            'name' => 'website-maintenance-toggle',
            'guard_name' => 'web',
        ])->first();

        if (! $permission) {
            $permission = new Permission([
                'name' => 'website-maintenance-toggle',
                'guard_name' => 'web',
            ]);
            // Kompatibel dengan dump database lama yang kehilangan AUTO_INCREMENT.
            $permission->id = ((int) Permission::query()->max('id')) + 1;
            $permission->save();
        }

        $permissionId = Permission::query()
            ->where('name', 'website-maintenance-toggle')
            ->where('guard_name', 'web')
            ->value('id');

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'Super Admin')
            ->pluck('id')
            ->each(fn ($roleId) => DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]));

        $bypassPermission = Permission::query()->where([
            'name' => 'website-maintenance-bypass',
            'guard_name' => 'web',
        ])->first();

        if (! $bypassPermission) {
            $bypassPermission = new Permission([
                'name' => 'website-maintenance-bypass',
                'guard_name' => 'web',
            ]);
            $bypassPermission->id = ((int) Permission::query()->max('id')) + 1;
            $bypassPermission->save();
        }

        $bypassPermissionId = Permission::query()
            ->where('name', 'website-maintenance-bypass')
            ->where('guard_name', 'web')
            ->value('id');

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Super Admin', 'BAAK', 'baak'])
            ->pluck('id')
            ->each(fn ($roleId) => DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $bypassPermissionId,
                'role_id' => $roleId,
            ]));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionIds = Permission::query()
            ->whereIn('name', ['website-maintenance-toggle', 'website-maintenance-bypass'])
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            Permission::query()->whereIn('id', $permissionIds)->delete();
        }

        if (Schema::hasColumn('settings', 'website_down')) {
            Schema::table('settings', fn (Blueprint $table) => $table->dropColumn('website_down'));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
