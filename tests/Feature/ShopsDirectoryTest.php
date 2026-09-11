<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProviderProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopsDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_shops_index_lists_only_approved_providers_with_active_products(): void
    {
        $withProducts = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => 'Ali Hardware']);
        Product::factory()->for($withProducts, 'providerProfile')->create(['is_active' => true]);

        $noProducts = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => 'No Products Shop']);

        $pendingProvider = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => 'Pending Shop', 'status' => ProviderProfile::STATUS_PENDING]);
        Product::factory()->for($pendingProvider, 'providerProfile')->create(['is_active' => true]);

        $response = $this->get('/shops');

        $response->assertOk();
        $response->assertSee('Ali Hardware');
        $response->assertDontSee('No Products Shop');
        $response->assertDontSee('Pending Shop');
    }

    public function test_shop_show_lists_that_shops_products_and_scopes_category_dropdown(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => "Bilal's Shop"]);
        $otherProvider = ProviderProfile::factory()->sellsProducts()->create();

        $categoryWithProduct = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing-shop-test', 'is_active' => true]);
        $categoryWithoutProduct = Category::create(['name' => 'Electrical', 'slug' => 'electrical-shop-test', 'is_active' => true]);

        $ownProduct = Product::factory()->for($provider, 'providerProfile')->create(['name' => 'Pipe Wrench', 'category_id' => $categoryWithProduct->id]);
        $otherProduct = Product::factory()->for($otherProvider, 'providerProfile')->create(['name' => 'Drill Bit']);

        $response = $this->get(route('shops.show', $provider));

        $response->assertOk();
        $response->assertSee("Bilal's Shop");
        $response->assertSee('Pipe Wrench');
        $response->assertDontSee('Drill Bit');
        $response->assertSee('Plumbing');
        $response->assertDontSee('Electrical');
    }

    public function test_shop_show_404s_for_a_provider_with_no_active_products(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $this->get(route('shops.show', $provider))->assertNotFound();
    }

    public function test_homepage_shows_shops_section_with_view_all_link(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create(['shop_name' => 'Homepage Test Shop']);
        Product::factory()->for($provider, 'providerProfile')->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Homepage Test Shop');
        $response->assertSee(route('shops.index'), false);
    }

    public function test_header_nav_links_to_shops_not_products(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('shops.index'), false);
    }
}
