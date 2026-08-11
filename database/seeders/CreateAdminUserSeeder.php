<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password || strlen($password) < 16) {
            throw new \RuntimeException(
                'Set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD (minimum 16 characters) before creating an admin.'
            );
        }

        $user = User::create(['name' => 'hadi21',
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $role = Role::create(['name' => 'Admin']);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
