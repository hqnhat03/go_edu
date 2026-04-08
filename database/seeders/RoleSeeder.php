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
        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);

        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        $manageUsers = Permission::create(['name' => 'manage users']);

        $admin->givePermissionTo($manageUsers);
        $adminUser->assignRole($admin);
    }
}
