<?php

namespace Database\Factories;

use App\AnalysisSection;
use App\Models\FaceAnalysis;
use App\Models\Order;
use App\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'face_analysis_id' => FaceAnalysis::factory(),
            'section' => AnalysisSection::TamDinh,
            'subtotal' => 49000,
            'discount' => 0,
            'amount' => 49000,
            'currency' => 'VND',
            'status' => OrderStatus::Pending,
        ];
    }

    /**
     * Use the given section.
     */
    public function section(AnalysisSection $section, int $amount = 49000): static
    {
        return $this->state(fn (array $attributes) => [
            'section' => $section,
            'subtotal' => $amount,
            'amount' => $amount,
        ]);
    }

    /**
     * Indicate that the order buys every paid section.
     */
    public function fullPackage(): static
    {
        return $this->state(fn (array $attributes) => [
            'section' => null,
            'subtotal' => 199000,
            'amount' => 199000,
        ]);
    }

    /**
     * Indicate that the order has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'payment_method' => 'bank_transfer',
            'transaction_id' => fake()->uuid(),
            'paid_at' => now(),
        ]);
    }
}
