<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ho = ["Nguyễn", "Trần", "Lê", "Phạm", "Hoàng", "Huỳnh", "Phan", "Vũ", "Võ", "Đặng"];
        $tenDem = ["Văn", "Thị", "Hữu", "Quang", "Minh", "Đức", "Ngọc", "Thanh"];
        $ten = ["An", "Bình", "Chi", "Dũng", "Hà", "Hải", "Hùng", "Lan", "Linh", "Trang", "Tuấn"];


        return [
            'name' => fake()->randomElement($ho) . ' ' .
                fake()->randomElement($tenDem) . ' ' .
                fake()->randomElement($ten),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->date('Y-m-d', '2000-01-01'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'avatar' => 'https://i.pravatar.cc/150?u=' . fake()->numberBetween(1, 1000),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
