<?php

namespace Database\Factories;

use App\Models\Order;
use App\OrderPaymentMethod;
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
        $subtotal = fake()->numberBetween(2, 30) * 50000;

        return [
            'customer_name' => fake()->name(),
            'phone' => '0'.fake()->numerify('9########'),
            'address' => fake()->address(),
            'note' => null,
            'payment_method' => OrderPaymentMethod::Cod,
            'subtotal' => $subtotal,
            'shipping_fee' => OrderPaymentMethod::DEFAULT_SHIPPING_FEE,
            'total' => $subtotal + OrderPaymentMethod::DEFAULT_SHIPPING_FEE,
            'status' => OrderStatus::New,
        ];
    }

    /**
     * Chuyển khoản online, miễn phí giao hàng, chưa nhận tiền.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_method' => OrderPaymentMethod::Online,
            'shipping_fee' => 0,
            'total' => $attributes['subtotal'],
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'paid_at' => now(),
        ]);
    }
}
