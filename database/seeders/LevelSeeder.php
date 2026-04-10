<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $subject_THCSs = Subject::where("training_level", "THCS")->get('id');
        $subject_THPTs = Subject::where("training_level", "THPT")->get('id');

        foreach ($subject_THCSs as $subject_THCS) {
            for ($i = 6; $i <= 9; $i++) {
                Level::create([
                    "subject_id" => $subject_THCS->id,
                    "level" => "Lớp " . $i,
                    "level_normalized" => "lop " . $i,
                    "status" => "published",
                    "created_by" => 1,
                ]);
            }
        }
        foreach ($subject_THPTs as $subject_THPT) {
            for ($i = 10; $i <= 12; $i++) {
                Level::create([
                    "subject_id" => $subject_THPT->id,
                    "level" => "Lớp " . $i,
                    "level_normalized" => "lop " . $i,
                    "status" => "published",
                    "created_by" => 1,
                ]);
            }
        }
    }
}
