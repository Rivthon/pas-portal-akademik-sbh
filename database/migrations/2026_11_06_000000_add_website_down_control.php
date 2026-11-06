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
            ->whereIn('name', ['Admin', 'Super Admin'])
            ->pluck('id')
            ->each(fn ($roleId) => DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionId = Permission::query()
            ->where('name', 'website-maintenance-toggle')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            Permission::query()->whereKey($permissionId)->delete();
        }

        if (Schema::hasColumn('settings', 'website_down')) {
            Schema::table('settings', fn (Blueprint $table) => $table->dropColumn('website_down'));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
