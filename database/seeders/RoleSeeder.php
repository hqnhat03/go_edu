<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
            'guest',
            'student',
            'teacher',
            'guardian',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
        }

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => bcrypt('password')
            ]
        );

        $superAdminUser = User::updateOrCreate(
            ['email' => 'super_admin@gmail.com'],
            [
                'name' => 'super_admin',
                'password' => bcrypt('password')
            ]
        );

        $admin = Role::where('name', 'admin')->first();
        $superAdmin = Role::where('name', 'super_admin')->first();

        $admin->syncPermissions(Permission::all());

        $superAdmin->syncPermissions(Permission::all());
        $adminUser->assignRole($admin);
        $superAdminUser->assignRole($superAdmin);
    }
}
