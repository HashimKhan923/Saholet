<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Smoke tests only — confirms each new public Blade view renders without error. */
class ShopBrowsingViewsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_index_renders_for_guests_and_with_filters(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Product::factory()->for($provider, 'providerProfile')->create();

        $this->get('/shop')->assertOk();
        $this->get('/shop?q=test')->assertOk();
        $this->get('/shop?provider=' . $provider->id)->assertOk();
    }

    public function test_shop_show_renders_for_guest_and_consumer(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();

        $this->get("/shop/{$product->id}")->assertOk();

        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $this->actingAs($consumer)->get("/shop/{$product->id}")->assertOk();
    }
}
