<?php

namespace Database\Factories;

use App\Models\Expert;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Expert>
 */
class ExpertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Thầy '.fake()->unique()->name();

        return [
            'user_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'glyph' => '相',
            'years_experience' => fake()->numberBetween(5, 40),
            'sessions_count' => fake()->numberBetween(50, 2000),
            'email' => fake()->safeEmail(),
            'phone' => '0'.fake()->numerify('9########'),
            'address' => fake()->address(),
            'description' => fake()->paragraphs(3, true),
            'is_active' => true,
            'sort' => 0,
        ];
    }

    /**
     * Đang ẩn, không hiện ngoài trang chủ.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
