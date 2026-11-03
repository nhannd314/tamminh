<?php

namespace Database\Factories;

use App\FiveElement;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state (đang bán, một mức giá).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'product_category_id' => ProductCategory::factory(),
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->unique()->bothify('TH-??###')),
            'glyph' => '福',
            'summary' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'elements' => fake()->randomElements(array_column(FiveElement::cases(), 'value'), 2),
            'price' => fake()->numberBetween(20, 300) * 10000,
            'stock' => 10,
            'sold_count' => fake()->numberBetween(0, 500),
            'is_active' => true,
            'sort' => 0,
        ];
    }

    /**
     * Đang ẩn, không hiện ra cửa hàng.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
