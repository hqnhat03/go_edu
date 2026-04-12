<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $range = range(6, 12);
        $student_levels = collect($range)->map(function ($level) {
            return [
                'level' => "Lớp " . $level,
                'slug' => Str::slug("Lớp " . $level),
                'status' => "published",
                'education_level' => "THCS - THPT",
            ];
        });
        $employee_levels = collect([
            "Cơ bản",
            "Trung cấp",
            "Nâng cao",
        ])->map(function ($level) {
            return [
                'level' => $level,
                'slug' => Str::slug($level),
                'status' => "published",
                'education_level' => "CĐ - ĐH",
            ];
        });
        $levels = $student_levels->concat($employee_levels);
        Level::insert($levels->toArray());
    }
}
