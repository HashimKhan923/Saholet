<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopListingPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_providers_own_products_page_paginates(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Product::factory()->for($provider, 'providerProfile')->count(20)->create();

        $response = $this->actingAs($provider->user)->get(route('provider.products.index'));

        $response->assertOk();
        $this->assertCount(15, $response->viewData('products'));
        $this->assertSame(20, $response->viewData('products')->total());
    }

    public function test_consumer_wishlist_paginates(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $products = Product::factory()->count(20)->create();
        foreach ($products as $product) {
            WishlistItem::create(['user_id' => $consumer->id, 'product_id' => $product->id]);
        }

        $response = $this->actingAs($consumer)->get(route('consumer.wishlist.index'));

        $response->assertOk();
        $this->assertCount(16, $response->viewData('products'));
        $this->assertSame(20, $response->viewData('products')->total());
    }
}
