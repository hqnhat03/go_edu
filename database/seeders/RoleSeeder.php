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
        Role::insert(collect($roles)->map(function ($role) {
            return [
                'name' => $role,
                'guard_name' => 'api',
            ];
        })->toArray());

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

        $admin = Role::where('name', 'admin')->first();
        $superAdmin = Role::where('name', 'super_admin')->first();

        $admin->givePermissionTo([
            'teacher_create',
            'teacher_list',
            'teacher_detail',
            'teacher_edit',
            'teacher_delete',
            'admin_create',
            'admin_list',
            'admin_detail',
            'admin_edit',
            'admin_delete',
            'student_create',
            'student_list',
            'student_detail',
            'student_edit',
            'student_delete',
            'course_create',
            'course_list',
            'course_detail',
            'course_edit',
            'course_delete',
            'class_create',
            'class_list',
            'class_detail',
            'class_edit',
            'class_delete',
            'news_create',
            'news_list',
            'news_detail',
            'news_edit',
            'news_delete',
            'grade_create',
            'grade_list',
            'grade_detail',
            'grade_edit',
            'grade_delete',
            'student_in_course_list',
            'student_in_course_detail',
            'student_in_course_edit',
            'student_in_course_delete',
            'subject_list',
            'subject_create',
            'subject_edit',
            'subject_delete',
            'level_list',
            'level_create',
            'level_edit',
            'level_delete',
            'role_list',
            'role_create',
            'role_edit',
            'role_delete',
            'guardian_list',
            'guardian_create',
            'guardian_detail',
            'guardian_edit',
            'guardian_delete',
        ]);
        $superAdmin->syncPermissions(Permission::all());
        $adminUser->assignRole($admin);
        $superAdminUser->assignRole($superAdmin);
    }
}
