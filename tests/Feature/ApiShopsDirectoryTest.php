<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiShopsDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_shops_index_lists_only_approved_providers_with_active_products(): void
    {
        $withProducts = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => 'Ali Hardware']);
        Product::factory()->for($withProducts, 'providerProfile')->create(['is_active' => true]);

        $noProducts = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => 'No Products Shop']);

        $response = $this->getJson('/api/shops');

        $response->assertOk()
            ->assertJsonPath('shops.0.shop.shop_name', 'Ali Hardware')
            ->assertJsonCount(1, 'shops');
    }

    public function test_api_shop_show_returns_shop_products_and_scoped_categories(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => "Bilal's Shop"]);
        $category = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing-api-shop-test', 'is_active' => true]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/shops/{$provider->id}");

        $response->assertOk()
            ->assertJsonPath('shop.shop.shop_name', "Bilal's Shop")
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('categories.0.name', 'Plumbing');
    }

    public function test_api_shop_show_404s_for_provider_with_no_products(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $this->getJson("/api/shops/{$provider->id}")->assertNotFound();
    }

    public function test_api_provider_can_update_shop_profile_with_logo(): void
    {
        Storage::fake('public');
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Sanctum::actingAs($provider->user);

        $response = $this->postJson('/api/provider/shop-profile', [
            'shop_name' => "Ali's Hardware",
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ]);

        $response->assertOk()->assertJsonPath('provider.shop.shop_name', "Ali's Hardware");

        $provider->refresh();
        $this->assertSame("Ali's Hardware", $provider->shop_name);
        Storage::disk('public')->assertExists($provider->shop_logo);
        $this->assertNotNull($response->json('provider.shop.shop_logo_url'));
    }

    public function test_api_provider_can_search_orders_by_product_name(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $consumer = User::create([
            'name' => 'Ayesha Khan', 'email' => 'ayesha-api@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $order = Order::create([
            'reference' => 'ORD-APISRCH', 'consumer_id' => $consumer->id, 'provider_profile_id' => $provider->id,
            'fulfillment_method' => 'pickup', 'payment_method' => 'cash', 'status' => 'pending',
            'subtotal' => 1000, 'shipping_amount' => 0, 'total_amount' => 1000,
            'commission_rate' => 10, 'commission_amount' => 100, 'provider_amount' => 900,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_name' => 'Pipe Wrench', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        Sanctum::actingAs($provider->user);

        $this->getJson('/api/provider/orders?q=Wrench')
            ->assertOk()
            ->assertJsonPath('orders.0.reference', 'ORD-APISRCH');

        $this->getJson('/api/provider/orders?q=nonexistent')
            ->assertOk()
            ->assertJsonCount(0, 'orders');
    }
}
