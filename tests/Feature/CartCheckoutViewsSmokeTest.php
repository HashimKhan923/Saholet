<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Smoke tests only — confirms each new Blade view renders without error. */
class CartCheckoutViewsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_and_populated_cart_page_renders(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $this->actingAs($consumer)->get('/cart')->assertOk();

        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id]);

        $this->actingAs($consumer)->get('/cart')->assertOk();
    }

    public function test_checkout_page_renders_with_multi_provider_cart_and_addresses(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $consumer->addresses()->create(['label' => 'Home', 'address' => '1 Main St', 'city' => 'Karachi', 'is_default' => true]);

        $providerA = ProviderProfile::factory()->sellsProducts()->create();
        $providerB = ProviderProfile::factory()->sellsProducts()->create();
        $productA = Product::factory()->for($providerA, 'providerProfile')->create();
        $productB = Product::factory()->for($providerB, 'providerProfile')->create();

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $productA->id]);
        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $productB->id]);

        $this->actingAs($consumer)->get('/checkout')->assertOk();
    }

    public function test_cart_page_renders_with_an_applied_coupon(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        \App\Models\Coupon::factory()->for($provider, 'providerProfile')->create(['code' => 'TESTCODE']);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id]);
        $this->actingAs($consumer)->post('/cart/coupon', ['provider_profile_id' => $provider->id, 'code' => 'TESTCODE']);

        $this->actingAs($consumer)->get('/cart')->assertOk();
    }

    public function test_checkout_page_renders_with_no_saved_addresses(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id]);

        $this->actingAs($consumer)->get('/checkout')->assertOk();
    }

    public function test_checkout_page_renders_with_applied_coupon_free_shipping_and_pickup_only_providers(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $consumer->addresses()->create(['label' => 'Home', 'address' => '1 Main St', 'city' => 'Karachi', 'is_default' => true]);

        $providerFree = ProviderProfile::factory()->create(['status' => 'approved', 'shipping_type' => 'free']);
        $productFree = Product::factory()->for($providerFree, 'providerProfile')->create();
        \App\Models\Coupon::factory()->for($providerFree, 'providerProfile')->create(['code' => 'FREE10']);

        $providerPickupOnly = ProviderProfile::factory()->create(['status' => 'approved', 'pickup_enabled' => true, 'pickup_hours' => 'Mon-Sat 9am-8pm']);
        $productPickup = Product::factory()->for($providerPickupOnly, 'providerProfile')->create();

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $productFree->id]);
        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $productPickup->id]);
        $this->actingAs($consumer)->post('/cart/coupon', ['provider_profile_id' => $providerFree->id, 'code' => 'FREE10']);

        $this->actingAs($consumer)->get('/checkout')->assertOk();
    }
}
