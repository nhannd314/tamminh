<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Affiliate>
 */
class AffiliateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_name' => null,
            'suspended_at' => null,
        ];
    }

    /**
     * Đã khai báo tài khoản nhận hoa hồng.
     */
    public function withBankAccount(): static
    {
        return $this->state(fn (array $attributes): array => [
            'bank_name' => 'Vietcombank',
            'bank_account_number' => fake()->numerify('##########'),
            'bank_account_name' => 'NGUYEN VAN AN',
        ]);
    }

    /**
     * Bị admin khoá.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'suspended_at' => now(),
        ]);
    }
}
