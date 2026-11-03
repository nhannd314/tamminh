<?php

namespace Database\Factories;

use App\AffiliateCommissionStatus;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateCommission>
 */
class AffiliateCommissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'commissionable_type' => 'order',
            'commissionable_id' => Order::factory(),
            'base_amount' => 500000,
            'rate_percent' => 10,
            'amount' => 50000,
            'status' => AffiliateCommissionStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AffiliateCommissionStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AffiliateCommissionStatus::Paid,
            'approved_at' => now(),
            'paid_at' => now(),
        ]);
    }
}
