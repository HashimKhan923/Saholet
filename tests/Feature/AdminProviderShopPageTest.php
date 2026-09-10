<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProviderShopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_a_providers_shop_page_with_products_orders_and_rating(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'shop-page-admin@example.com', 'phone' => '+923001117777',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['name' => 'Test Wrench']);
        $order = Order::factory()->for($provider, 'providerProfile')->create();
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        ProductReview::create(['product_id' => $product->id, 'order_id' => $order->id, 'user_id' => $consumer->id, 'rating' => 4]);

        $response = $this->actingAs($admin)->get(route('admin.providers.shop', $provider));

        $response->assertOk()
            ->assertSee('Test Wrench')
            ->assertSee($order->reference)
            ->assertSee('4.0');
    }

    public function test_products_orders_and_reviews_paginate_independently(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'shop-pagination-admin@example.com', 'phone' => '+923001115555',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $products = Product::factory()->for($provider, 'providerProfile')->count(20)->create();
        Order::factory()->for($provider, 'providerProfile')->count(20)->create();
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $order = Order::factory()->for($provider, 'providerProfile')->create();
        foreach ($products->take(20) as $product) {
            ProductReview::create([
                'product_id' => $product->id,
                'order_id' => Order::factory()->for($provider, 'providerProfile')->create()->id,
                'user_id' => $consumer->id,
                'rating' => 5,
            ]);
        }

        $countRowsShown = fn (string $html) => collect($products)->filter(
            fn ($p) => str_contains($html, 'href="' . route('admin.products.show', $p) . '"')
        )->count();

        $page1 = $this->actingAs($admin)->get(route('admin.providers.shop', $provider));
        $page1->assertOk();
        $this->assertSame(15, $countRowsShown($page1->getContent()), '15 products should show on page 1 out of 20 total.');
        $page1->assertSee('products_page=2', false);

        $page2 = $this->actingAs($admin)->get(route('admin.providers.shop', [$provider, 'products_page' => 2]));
        $page2->assertOk();
        $this->assertSame(5, $countRowsShown($page2->getContent()), 'the remaining 5 products should show on page 2.');
    }

    public function test_the_shop_card_on_the_provider_profile_links_to_the_shop_page(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'shop-card-admin@example.com', 'phone' => '+923001116666',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Product::factory()->for($provider, 'providerProfile')->create();

        $this->actingAs($admin)->get(route('admin.providers.show', $provider))
            ->assertOk()
            ->assertSee(route('admin.providers.shop', $provider), false);
    }
}
