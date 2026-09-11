<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShopOversightTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'oversight-admin@example.com', 'phone' => '+923001119999',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
    }

    public function test_admin_can_deactivate_and_delete_a_product(): void
    {
        $admin = $this->admin();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['is_active' => true]);

        $this->actingAs($admin)->post("/admin/products/{$product->id}/toggle-active", [
            'deactivation_reason' => 'Photos are misleading.',
            'reactivation_instructions' => 'Upload real photos and resubmit.',
        ])->assertRedirect();
        $product->refresh();
        $this->assertFalse($product->is_active);
        $this->assertSame('Photos are misleading.', $product->deactivation_reason);
        $this->assertSame('Upload real photos and resubmit.', $product->reactivation_instructions);

        $this->actingAs($admin)->post("/admin/products/{$product->id}/toggle-active")->assertRedirect();
        $product->refresh();
        $this->assertTrue($product->is_active);
        $this->assertNull($product->deactivation_reason);

        $this->actingAs($admin)->delete("/admin/products/{$product->id}")->assertRedirect();
        $this->assertSame(0, Product::count());
    }

    public function test_admin_views_render_for_products_and_orders(): void
    {
        $admin = $this->admin();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $order = \App\Models\Order::factory()->for($provider, 'providerProfile')->create();
        $order->items()->create(['product_name' => 'X', 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get("/admin/products/{$product->id}")->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
        $this->actingAs($admin)->get('/admin/orders?status=pending')->assertOk();
        $this->actingAs($admin)->get("/admin/orders/{$order->id}")->assertOk();
    }

    public function test_admin_can_search_orders_by_product_name(): void
    {
        $admin = $this->admin();
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $match = \App\Models\Order::factory()->for($provider, 'providerProfile')->create(['reference' => 'ORD-MATCHIT']);
        $match->items()->create(['product_name' => 'Pipe Wrench', 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

        $other = \App\Models\Order::factory()->for($provider, 'providerProfile')->create(['reference' => 'ORD-OTHERONE']);
        $other->items()->create(['product_name' => 'Screwdriver', 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

        $response = $this->actingAs($admin)->get('/admin/orders?q=Wrench');

        $response->assertOk();
        $response->assertSee('ORD-MATCHIT');
        $response->assertDontSee('ORD-OTHERONE');
    }

    public function test_staff_without_the_products_permission_cannot_moderate_products(): void
    {
        $staff = User::create([
            'name' => 'Staff', 'email' => 'no-products-staff@example.com', 'phone' => '+923001118888',
            'role' => User::ROLE_STAFF, 'password' => 'password', 'permissions' => [],
        ]);
        $product = Product::factory()->create();

        $this->actingAs($staff)->get('/admin/products')->assertForbidden();
        $this->actingAs($staff)->post("/admin/products/{$product->id}/toggle-active")->assertForbidden();
    }
}
