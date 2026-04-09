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
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        $superAdminUser = User::create([
            'name' => 'super_admin',
            'email' => 'super_admin@gmail.com',
            'password' => 'password'
        ]);


        $superAdmin = Role::create(['name' => 'super_admin']);
        $admin = Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
        Role::create(['name' => 'student']);
        Role::create(['name' => 'teacher']);

        $manageUsers = Permission::create(['name' => 'manage users']);

        $admin->givePermissionTo($manageUsers);
        $adminUser->assignRole($admin);
    }
}
