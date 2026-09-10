<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderShopManagementTest extends TestCase
{
    use RefreshDatabase;

    private function approvedProvider(): array
    {
        $profile = ProviderProfile::factory()->create();

        return [$profile->user, $profile];
    }

    // ─── Shop settings (web) ────────────────────────────────────────

    public function test_provider_can_configure_flat_shipping(): void
    {
        [$user, $profile] = $this->approvedProvider();

        $this->actingAs($user)->post('/provider/shop-settings', [
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 250,
        ])->assertRedirect();

        $this->assertSame('flat', $profile->fresh()->shipping_type);
        $this->assertSame('250.00', (string) $profile->fresh()->shipping_flat_rate);
    }

    public function test_provider_can_configure_free_delivery(): void
    {
        [$user, $profile] = $this->approvedProvider();

        $this->actingAs($user)->post('/provider/shop-settings', [
            'shipping_type' => 'free',
        ])->assertRedirect();

        $this->assertSame('free', $profile->fresh()->shipping_type);
        $this->assertNull($profile->fresh()->shipping_flat_rate);
    }

    // ─── Products (web) ─────────────────────────────────────────────

    public function test_product_cannot_be_added_until_a_fulfillment_method_is_configured(): void
    {
        [$user] = $this->approvedProvider();

        $this->actingAs($user)->post('/provider/products', [
            'name' => 'Pipe Wrench',
            'price' => 1500,
            'stock_quantity' => 10,
        ])->assertStatus(422);

        $this->assertSame(0, Product::count());
    }

    public function test_provider_can_add_a_product_once_pickup_is_enabled(): void
    {
        [$user, $profile] = $this->approvedProvider();
        $profile->update(['pickup_enabled' => true]);

        $this->actingAs($user)->post('/provider/products', [
            'name' => 'Pipe Wrench',
            'description' => 'Heavy duty wrench',
            'price' => 1500,
            'stock_quantity' => 10,
        ])->assertRedirect(route('provider.products.index'));

        $product = Product::firstOrFail();
        $this->assertSame($profile->id, $product->provider_profile_id);
        $this->assertSame('pipe-wrench', $product->slug);
        $this->assertTrue($product->is_active);
    }

    public function test_provider_can_update_and_delete_their_own_product(): void
    {
        [$user, $profile] = $this->approvedProvider();
        $profile->update(['pickup_enabled' => true]);
        $product = \App\Models\Product::factory()->for($profile, 'providerProfile')->create();

        $this->actingAs($user)->put("/provider/products/{$product->id}", [
            'name' => $product->name,
            'price' => 999,
            'stock_quantity' => 5,
            'is_active' => 0,
        ])->assertRedirect();

        $product->refresh();
        $this->assertSame('999.00', (string) $product->price);
        $this->assertFalse($product->is_active);

        $this->actingAs($user)->delete("/provider/products/{$product->id}")->assertRedirect();
        $this->assertSame(0, Product::count());
    }

    public function test_a_provider_cannot_edit_another_providers_product(): void
    {
        [$user] = $this->approvedProvider();
        [, $otherProfile] = $this->approvedProvider();
        $product = Product::factory()->for($otherProfile, 'providerProfile')->create();

        $this->actingAs($user)->put("/provider/products/{$product->id}", [
            'name' => 'Hacked', 'price' => 1, 'stock_quantity' => 1,
        ])->assertForbidden();

        $this->actingAs($user)->delete("/provider/products/{$product->id}")->assertForbidden();
    }

    public function test_non_approved_provider_cannot_manage_shop(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $this->actingAs($user)->post('/provider/shop-settings', ['shipping_type' => 'flat', 'shipping_flat_rate' => 100])
            ->assertForbidden();
    }

    // ─── API mirror ─────────────────────────────────────────────────

    public function test_api_provider_can_configure_shop_and_manage_products(): void
    {
        [$user, $profile] = $this->approvedProvider();
        Sanctum::actingAs($user);

        $this->putJson('/api/provider/shop-settings', [
            'shipping_type' => 'percentage',
            'shipping_percentage' => 5,
        ])->assertOk()
            ->assertJsonPath('provider.shop.shipping_type', 'percentage');

        $this->postJson('/api/provider/products', [
            'name' => 'Drill Bit Set',
            'price' => 2200,
            'stock_quantity' => 20,
        ])->assertCreated()
            ->assertJsonPath('product.name', 'Drill Bit Set');

        $product = Product::firstOrFail();

        $this->getJson('/api/provider/products')->assertOk()
            ->assertJsonCount(1, 'products');

        $this->deleteJson("/api/provider/products/{$product->id}")->assertOk();
        $this->assertSame(0, Product::count());
    }

    public function test_api_rejects_product_management_for_unauthenticated_requests(): void
    {
        $this->postJson('/api/provider/products', ['name' => 'X', 'price' => 1, 'stock_quantity' => 1])
            ->assertUnauthorized();
    }
}
