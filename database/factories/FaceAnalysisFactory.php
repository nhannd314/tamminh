<?php

namespace Database\Factories;

use App\AnalysisStatus;
use App\Gender;
use App\Models\FaceAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaceAnalysis>
 */
class FaceAnalysisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'gender' => fake()->randomElement(Gender::cases()),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'birth_hour' => fake()->numberBetween(0, 23),
            'birth_minute' => fake()->numberBetween(0, 59),
            'portrait_path' => 'portraits/'.fake()->uuid().'.jpg',
            'status' => AnalysisStatus::Pending,
            'ip_address' => fake()->ipv4(),
            'terms_accepted_at' => now(),
        ];
    }

    /**
     * Indicate that Gemini has finished analysing the free sections.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Completed,
            'ai_model' => 'gemini-2.5-flash',
            'analyzed_at' => now(),
        ]);
    }

    /**
     * Indicate that the analysis failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Failed,
            'failure_reason' => 'Không nhận diện được khuôn mặt trong ảnh.',
        ]);
    }
}
