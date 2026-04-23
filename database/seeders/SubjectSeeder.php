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
                "slug" => "toan-hoc",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Vật lý",
                "slug" => "vat-ly",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Hóa học",
                "slug" => "hoa-hoc",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Ngữ văn",
                "slug" => "ngu-van",
                "category" => "Khoa học xã hội",
                "status" => "published",
            ],
            [
                "name" => "Lịch sử - Địa lý",
                "slug" => "lich-su",
                "category" => "Khoa học xã hội",
                "status" => "published",
            ],
            [
                "name" => "Tiếng Anh",
                "slug" => "tieng-anh",
                "category" => "Ngoại ngữ",
                "status" => "published",
            ],
        ];

        Subject::insert($subjects);
    }
}
