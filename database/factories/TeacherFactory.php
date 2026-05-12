<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expertises = ['Toán học', 'Vật lý', 'Hóa học', 'Sinh học', 'Tin học', 'Tiếng Anh', 'Ngữ văn', 'Lịch sử', 'Địa lý'];
        $expertise = fake()->randomElement($expertises);
        
        return [
            'user_id' => User::factory(),
            'expertise' => $expertise,
            'experience' => fake()->numberBetween(1, 15),
            'nationality' => 'Việt Nam',
            'bio' => 'Giáo viên chuyên ngành ' . $expertise . ' với nhiều năm kinh nghiệm giảng dạy.',
            'target_student' => 'student',
        ];
    }
}
