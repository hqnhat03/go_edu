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
        //
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            $user->assignRole("student");
            Student::factory()->create([
                "user_id" => $user->id,
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $user->assignRole("guardian");
            Guardian::create([
                "user_id" => $user->id,
            ]);
        }

        $guardians = Guardian::pluck('id')->toArray();
        $students = Student::pluck('id')->toArray();

        foreach ($guardians as $guardianId) {
            $randomStudents = collect($students)->random(rand(1, 2));

            foreach ($randomStudents as $studentId) {
                DB::table('student_guardians')->insert([
                    'guardian_id' => $guardianId,
                    'student_id' => $studentId,
                ]);
            }
        }
    }
}
