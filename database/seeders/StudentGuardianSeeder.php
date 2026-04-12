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

        User::factory()->count(30)->create();
        $userIds = DB::table('users')->latest('id')->limit(30)->pluck('id')->toArray();
        $studentUserIds = array_slice($userIds, 0, 20);
        $students = [];
        foreach ($studentUserIds as $userId) {
            $students[] = [
                'user_id' => $userId,
            ];
        }
        Student::insert($students);

        $guardianUserIds = array_slice($userIds, 20, 10);
        $guardians = [];
        foreach ($guardianUserIds as $userId) {
            $guardians[] = [
                'user_id' => $userId,
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
