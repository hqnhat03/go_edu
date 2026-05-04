<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentGuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::factory()->count(20)->create();
        $userIds = DB::table('users')->latest('id')->limit(20)->pluck('id')->toArray();
        $studentUserIds = array_slice($userIds, 0, 10);
        $students = [];
        foreach ($studentUserIds as $userId) {
            $createdAt = fake()->dateTimeBetween('-6 months', 'now');
            $students[] = [
                'user_id' => $userId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }
        Student::insert($students);

        $guardianUserIds = array_slice($userIds, 10, 20);
        $guardians = [];
        foreach ($guardianUserIds as $userId) {
            $guardians[] = [
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Guardian::insert($guardians);

        $studentIds = DB::table('students')->pluck('id')->toArray();
        $guardianIds = DB::table('guardians')->pluck('id')->toArray();

        $pivot = [];
        foreach ($guardianIds as $guardianId) {
            $randomStudents = collect($studentIds)->random(rand(1, 2));

            foreach ($randomStudents as $studentId) {
                $pivot[] = [
                    'guardian_id' => $guardianId,
                    'student_id' => $studentId,
                ];
            }
        }
        DB::table('student_guardians')->insert($pivot);

    }
}
