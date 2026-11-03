<?php

namespace Database\Factories;

use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\Models\AnalysisOrder;
use App\Models\FaceAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisOrder>
 */
class AnalysisOrderFactory extends Factory
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
            'subtotal' => 20000,
            'discount' => 0,
            'amount' => 20000,
            'currency' => 'VND',
            'status' => AnalysisOrderStatus::Pending,
        ];
    }

    /**
     * Use the given section.
     */
    public function section(AnalysisSection $section, int $amount = 20000): static
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
            'subtotal' => 120000,
            'discount' => 24000,
            'amount' => 96000,
        ]);
    }

    /**
     * Indicate that the order has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisOrderStatus::Paid,
            'payment_method' => 'bank_transfer',
            'transaction_id' => fake()->uuid(),
            'paid_at' => now(),
        ]);
    }
}
