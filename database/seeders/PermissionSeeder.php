<?php

namespace Database\Seeders;

use Arr;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Định nghĩa các nhóm quyền dựa trên màn hình của bạn
        $resources = [
            'dashboard',
            'admin_list',
            'admin_detail',
            'admin_create',
            'admin_edit',
            'admin_delete',
            'teacher_list',
            'teacher_detail',
            'teacher_create',
            'teacher_edit',
            'teacher_delete',
            'student_list',
            'student_detail',
            'student_create',
            'student_edit',
            'student_delete',
            'role_management', // Cho phần "Phân quyền màn hình"
            'course_list',
            'course_detail',
            'course_edit',
            'course_create',
            'course_delete',
            'class_list',
            'class_detail',
            'class_edit',
            'class_create',
            'class_delete',
            'news_list',
            'news_detail',
            'news_edit',
            'news_create',
            'news_delete',
            'grade_list',
            'grade_detail',
            'grade_edit',
            'grade_create',
            'grade_delete',
            'student_in_course_list',
            'student_in_course_detail',
            'student_in_course_edit',
            'student_in_course_delete',
            'subject_list',
            'subject_edit',
            'subject_create',
            'subject_delete',
            'level_list',
            'level_edit',
            'level_create',
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
        ];



        Permission::insert(collect($resources)->map(function ($resource) {
            return [
                'name' => $resource,
                'guard_name' => 'api',
            ];
        })->toArray());
    }
}
