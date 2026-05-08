<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MassDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed 50 Courses
        \App\Models\Course::factory()->count(50)->create();

        // 2. Seed 50 Admins
        \App\Models\User::factory()->count(50)->create()->each(function ($user) {
            $user->assignRole('admin');
        });

        // 3. Seed 50 Students
        \App\Models\Student::factory()->count(50)->create()->each(function ($student) {
            $student->user->assignRole('student');
        });

        // 4. Seed 50 Guardians
        \App\Models\Guardian::factory()->count(50)->create()->each(function ($guardian) {
            $guardian->user->assignRole('guardian');
        });
    }
}
