<?php

namespace Tests\Unit;

use App\Models\ProviderProfile;
use App\Services\ShippingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private ShippingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ShippingCalculator();
    }

    public function test_flat_rate_ignores_subtotal(): void
    {
        $provider = ProviderProfile::factory()->create([
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 200,
        ]);

        $this->assertSame(200.0, $this->calculator->costFor($provider, 5000));
        $this->assertSame(200.0, $this->calculator->costFor($provider, 1));
    }

    public function test_percentage_rate_is_computed_from_subtotal(): void
    {
        $provider = ProviderProfile::factory()->create([
            'shipping_type' => 'percentage',
            'shipping_percentage' => 5,
        ]);

        $this->assertSame(50.0, $this->calculator->costFor($provider, 1000));
    }

    public function test_free_delivery_is_always_zero(): void
    {
        $provider = ProviderProfile::factory()->create(['shipping_type' => 'free']);

        $this->assertSame(0.0, $this->calculator->costFor($provider, 5000));
    }

    public function test_returns_null_when_provider_has_no_shipping_type(): void
    {
        $provider = ProviderProfile::factory()->create(['shipping_type' => null]);

        $this->assertNull($this->calculator->costFor($provider, 1000));
    }
}
