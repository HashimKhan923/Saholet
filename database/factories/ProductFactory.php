<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'provider_profile_id' => ProviderProfile::factory(),
            'name' => $name,
            'slug' => fn (array $attributes) => Product::generateSlug($name, $attributes['provider_profile_id']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 5000),
            'stock_quantity' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
