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
        $expertises = ['Toán học', 'Vật lý', 'Hóa học', 'Sinh học', 'Tin học'];
        foreach ($userIds as $userId) {
            $expertise = $expertises[array_rand($expertises)];
            $teachers[] = [
                'user_id' => $userId,
                'expertise' => $expertise,
                'experience' => 1,
                'nationality' => 'Việt Nam',
                'bio' => 'Giáo viên dạy ' . $expertise,
                'target_student' => 'student',
            ];
        }

        Teacher::insert($teachers);

        $user = User::create([
            'name' => 'teacher',
            'email' => 'teacher@gmail.com',
            'password' => 'password',
            'phone' => '0123456789',
            'address' => 'Hà Nội',
            'gender' => 'male',
            'status' => 'active',
            'date_of_birth' => '2000-01-01',
            'avatar' => null,
        ]);
        $user->assignRole('teacher');
        $user->teacher()->create([
            'expertise' => 'Toán học',
            'experience' => 1,
            'nationality' => 'Việt Nam',
            'bio' => 'Giáo viên dạy Toán học',
            'target_student' => 'student',
        ]);
    }
}
