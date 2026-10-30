<?php

namespace Database\Factories;

use App\AnalysisSection;
use App\Models\FaceAnalysis;
use App\Models\FaceAnalysisSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaceAnalysisSection>
 */
class FaceAnalysisSectionFactory extends Factory
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
            'type' => AnalysisSection::Overview,
            'content' => [
                'summary' => fake()->sentence(),
                'paragraphs' => fake()->paragraphs(3),
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Use the given section type.
     */
    public function type(AnalysisSection $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the section has been paid for.
     */
    public function unlocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'unlocked_at' => now(),
        ]);
    }

    /**
     * Indicate that the content has not been generated yet.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => null,
            'generated_at' => null,
        ]);
    }
}
