<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostCategory;
use App\PostTone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state (đã đăng).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'post_category_id' => PostCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'glyph' => '相',
            'tone' => fake()->randomElement(PostTone::cases()),
            'author_name' => 'Thầy '.fake()->lastName(),
            'published_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ];
    }

    /**
     * Bản nháp, chưa đăng.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }

    /**
     * Hẹn giờ đăng trong tương lai.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => now()->addDay()]);
    }
}
