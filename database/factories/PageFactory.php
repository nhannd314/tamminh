<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state (đang hiển thị).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => Str::ucfirst($title),
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'is_published' => true,
        ];
    }

    /**
     * Trang đang ẩn.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}
