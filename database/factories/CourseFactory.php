<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'target_student' => fake()->randomElement(['student', 'employee', 'all']),
            'price' => fake()->numberBetween(100, 1000) * 1000,
            'lesson_count' => fake()->numberBetween(10, 50),
            'completion_time' => fake()->numberBetween(5, 20),
            'image_url' => 'https://picsum.photos/seed/' . fake()->word() . '/800/600',
            'level_id' => \App\Models\Level::inRandomOrder()->first()?->id ?? 1,
            'subject_id' => \App\Models\Subject::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
