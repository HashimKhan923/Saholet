<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 10000);
        $shipping = fake()->randomElement([0, 150, 300]);

        return [
            'reference' => 'ORD-' . strtoupper(Str::random(6)),
            'consumer_id' => User::factory()->state(['role' => User::ROLE_CONSUMER]),
            'provider_profile_id' => ProviderProfile::factory()->sellsProducts(),
            'fulfillment_method' => Order::FULFILLMENT_DELIVERY,
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->randomElement(['Karachi', 'Lahore', 'Islamabad']),
            'subtotal' => $subtotal,
            'shipping_amount' => $shipping,
            'total_amount' => $subtotal + $shipping,
            'payment_method' => 'cash',
            'status' => Order::STATUS_PENDING,
        ];
    }

    public function pickup(): static
    {
        return $this->state(fn () => [
            'fulfillment_method' => Order::FULFILLMENT_PICKUP,
            'shipping_address' => null,
            'shipping_city' => null,
            'shipping_amount' => 0,
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn () => ['payment_method' => 'bank_transfer']);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
