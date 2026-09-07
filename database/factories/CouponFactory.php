<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'provider_profile_id' => ProviderProfile::factory(),
            'code' => strtoupper(Str::random(8)),
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10,
            'is_active' => true,
        ];
    }

    public function flat(float $amount): static
    {
        return $this->state(fn () => ['type' => Coupon::TYPE_FLAT, 'value' => $amount]);
    }

    public function percentage(float $percent): static
    {
        return $this->state(fn () => ['type' => Coupon::TYPE_PERCENTAGE, 'value' => $percent]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
