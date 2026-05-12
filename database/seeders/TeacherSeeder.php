<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 50 giáo viên ngẫu nhiên
        \App\Models\Teacher::factory()->count(50)->create()->each(function ($teacher) {
            $teacher->user->assignRole('teacher');
        });

        // Tạo 1 giáo viên cố định để test
        $user = User::updateOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'name' => 'teacher',
                'password' => bcrypt('password'),
                'phone' => '0123456789',
                'address' => 'Hà Nội',
                'gender' => 'male',
                'status' => 'active',
                'date_of_birth' => '2000-01-01',
                'avatar' => null,
            ]
        );
        $user->assignRole('teacher');
        
        if (!$user->teacher) {
            $user->teacher()->create([
                'expertise' => 'Toán học',
                'experience' => 1,
                'nationality' => 'Việt Nam',
                'bio' => 'Giáo viên dạy Toán học',
                'target_student' => 'student',
            ]);
        }
    }
}
