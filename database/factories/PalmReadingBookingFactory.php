<?php

namespace Database\Factories;

use App\Gender;
use App\Models\PalmReadingBooking;
use App\PalmReadingPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PalmReadingBooking>
 */
class PalmReadingBookingFactory extends Factory
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
            'birth_date' => fake()->dateTimeBetween('-70 years', '-16 years'),
            'phone' => '0'.fake()->numerify('9########'),
            'package' => fake()->randomElement(PalmReadingPackage::cases()),
            'contacted_at' => null,
        ];
    }

    /**
     * Admin đã liên hệ lại khách.
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'contacted_at' => now(),
        ]);
    }
}
