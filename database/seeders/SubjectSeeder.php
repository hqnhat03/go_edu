<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                "name" => "Toán học",
                "category" => "Tự nhiên",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Vật lý",
                "category" => "Tự nhiên",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Hóa học",
                "category" => "Tự nhiên",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Tin học",
                "category" => "Tự nhiên",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Tiếng Anh",
                "category" => "Xã hội",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Ngữ văn",
                "category" => "Xã hội",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Lịch sử",
                "category" => "Xã hội",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Địa lý",
                "category" => "Xã hội",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Giáo dục công dân",
                "category" => "Xã hội",
                "training_level" => "THCS",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Toán học",
                "category" => "Tự nhiên",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Vật lý",
                "category" => "Tự nhiên",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Hóa học",
                "category" => "Tự nhiên",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Tin học",
                "category" => "Tự nhiên",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Tiếng Anh",
                "category" => "Xã hội",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Ngữ văn",
                "category" => "Xã hội",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Lịch sử",
                "category" => "Xã hội",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Địa lý",
                "category" => "Xã hội",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
            [
                "name" => "Giáo dục công dân",
                "category" => "Xã hội",
                "training_level" => "THPT",
                "student_type" => "student",
                "status" => "published",
                "created_by" => 1,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

    }
}
