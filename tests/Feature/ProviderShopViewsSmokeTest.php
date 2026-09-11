<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProviderProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Smoke tests only — just confirms each new Blade view renders without error, not visual correctness. */
class ProviderShopViewsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_shop_settings_pages_render(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $this->actingAs($provider->user)->get('/provider/shop-settings/profile')->assertOk();
        $this->actingAs($provider->user)->get('/provider/shop-settings/shipping')->assertOk();
        $this->actingAs($provider->user)->get('/provider/shop-settings/pickup')->assertOk();
    }

    public function test_provider_products_index_and_create_pages_render(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Product::factory()->for($provider, 'providerProfile')->create();

        $this->actingAs($provider->user)->get('/provider/products')->assertOk();
        $this->actingAs($provider->user)->get('/provider/products/create')->assertOk();
    }

    public function test_provider_product_edit_page_renders(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();

        $this->actingAs($provider->user)->get("/provider/products/{$product->id}/edit")->assertOk();
    }

    public function test_provider_orders_index_and_show_pages_render_for_every_status(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        foreach (['pending', 'confirmed', 'ready', 'completed', 'cancelled'] as $status) {
            $order = Order::factory()->for($provider, 'providerProfile')->status($status)->create();
            $order->items()->create([
                'product_name' => 'Test item', 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100,
            ]);

            $this->actingAs($provider->user)->get("/provider/orders/{$order->id}")->assertOk();
        }

        $this->actingAs($provider->user)->get('/provider/orders')->assertOk();
        $this->actingAs($provider->user)->get('/provider/orders?status=pending')->assertOk();
    }
}
