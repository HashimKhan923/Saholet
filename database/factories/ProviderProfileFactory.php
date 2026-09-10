<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    protected $model = ProviderProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => User::ROLE_PROVIDER]),
            'business_name' => fake()->company(),
            'bio' => fake()->sentence(),
            'experience_years' => fake()->numberBetween(1, 15),
            'city' => fake()->randomElement(['Karachi', 'Lahore', 'Islamabad']),
            'address' => fake()->streetAddress(),
            'status' => ProviderProfile::STATUS_APPROVED,
            'commission_rate' => 10,
        ];
    }

    /** A provider ready to sell products: approved, with a flat shipping rate and pickup both enabled. */
    public function sellsProducts(): static
    {
        return $this->state(fn () => [
            'status' => ProviderProfile::STATUS_APPROVED,
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 150,
            'pickup_enabled' => true,
            'product_commission_rate' => 8,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['suspended_at' => now()]);
    }
}
