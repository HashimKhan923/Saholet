<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Confirms the discount/coupon line renders correctly on every order-show page. */
class OrderDiscountDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_line_renders_on_consumer_provider_and_admin_order_pages(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $coupon = Coupon::factory()->for($provider, 'providerProfile')->create(['code' => 'SHOWTEST']);
        $order = Order::factory()->for($provider, 'providerProfile')->create([
            'subtotal' => 1000,
            'coupon_id' => $coupon->id,
            'discount_amount' => 150,
            'total_amount' => 850,
        ]);
        $order->items()->create(['product_name' => 'X', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        $consumer = $order->consumer;
        $providerUser = $provider->user;
        $admin = User::create([
            'name' => 'Admin', 'email' => 'discount-admin@example.com', 'phone' => '+923001117777',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        $consumerResponse = $this->actingAs($consumer)->get("/orders/{$order->id}")->assertOk();
        $consumerResponse->assertSee('SHOWTEST');
        $consumerResponse->assertSee('150');

        $providerResponse = $this->actingAs($providerUser)->get("/provider/orders/{$order->id}")->assertOk();
        $providerResponse->assertSee('SHOWTEST');

        $adminResponse = $this->actingAs($admin)->get("/admin/orders/{$order->id}")->assertOk();
        $adminResponse->assertSee('SHOWTEST');
    }
}
