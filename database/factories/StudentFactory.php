<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grade = fake()->numberBetween(6, 12);

        $thcs = [
            "THCS Nguyễn Du",
            "THCS Trần Phú",
            "THCS Lê Lợi",
            "THCS Nguyễn Huệ",
            "THCS Phan Chu Trinh",
        ];

        $thpt = [
            "THPT Phan Châu Trinh",
            "THPT Trần Phú",
            "THPT Nguyễn Hiền",
            "THPT Hoàng Hoa Thám",
            "THPT Lê Quý Đôn",
        ];

        return [
            "student_type" => "student",
            "school" => $grade <= 9
                ? fake()->randomElement($thcs)
                : fake()->randomElement($thpt),
            "grade" => $grade,
            "work" => null,
            "position" => null,
        ];
    }
}
