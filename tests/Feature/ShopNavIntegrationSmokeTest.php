<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Smoke tests only — confirms pages touched by shop nav/dashboard/profile integration still render. */
class ShopNavIntegrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_for_guest_and_consumer_with_cart_items(): void
    {
        $this->get('/')->assertOk();

        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id]);

        $this->actingAs($consumer)->get('/')->assertOk();
    }

    public function test_consumer_dashboard_renders(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $this->actingAs($consumer)->get(route('consumer.dashboard'))->assertOk();
    }

    public function test_provider_profile_page_renders_with_and_without_products(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $this->get(route('providers.show', $provider))->assertOk();

        Product::factory()->for($provider, 'providerProfile')->create();
        $this->get(route('providers.show', $provider))->assertOk();
    }
}
