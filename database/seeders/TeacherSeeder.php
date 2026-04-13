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
        User::factory()->count(10)->create();
        $userIds = DB::table('users')->latest('id')->limit(10)->pluck('id')->toArray();
        $teachers = [];
        foreach ($userIds as $userId) {
            $teachers[] = [
                'user_id' => $userId,
                'expertise' => 'Toán học',
                'experience' => 1,
                'nationality' => 'Việt Nam',
                'bio' => 'Giáo viên dạy Toán',
                'target_student' => 'student',
            ];
        }
        Teacher::insert($teachers);
    }
}
