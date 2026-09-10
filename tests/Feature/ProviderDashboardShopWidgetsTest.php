<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderDashboardShopWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_out_of_stock_and_top_selling_products(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create([
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        $outOfStock = Product::factory()->for($provider, 'providerProfile')->outOfStock()->create(['name' => 'Empty Wrench']);
        $bestSeller = Product::factory()->for($provider, 'providerProfile')->create(['name' => 'Popular Bulb']);

        $order = Order::factory()->for($provider, 'providerProfile')->create(['status' => Order::STATUS_COMPLETED]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $bestSeller->id, 'product_name' => $bestSeller->name,
            'unit_price' => 100, 'quantity' => 7, 'line_total' => 700,
        ]);

        $response = $this->actingAs($provider->user)->get(route('provider.dashboard'));

        $response->assertOk()
            ->assertSee('Empty Wrench')
            ->assertSee('Popular Bulb')
            ->assertSee('7');
    }

    public function test_dashboard_hides_shop_widgets_when_provider_has_no_products(): void
    {
        $provider = ProviderProfile::factory()->create(['status' => ProviderProfile::STATUS_APPROVED]);

        $this->actingAs($provider->user)->get(route('provider.dashboard'))
            ->assertOk()
            ->assertDontSee('Out of stock');
    }
}
