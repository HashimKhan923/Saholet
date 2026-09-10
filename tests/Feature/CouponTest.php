<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private function consumer(): User
    {
        return User::create([
            'name' => 'Coupon Shopper', 'email' => 'couponshopper' . uniqid() . '@example.com',
            'phone' => '+92300' . random_int(1000000, 9999999), 'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
    }

    // ─── Provider coupon management ────────────────────────────────

    public function test_provider_can_create_and_deactivate_a_coupon(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $this->actingAs($provider->user)->post('/provider/coupons', [
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
        ])->assertRedirect();

        $coupon = Coupon::firstOrFail();
        $this->assertSame('SAVE10', $coupon->code);
        $this->assertSame($provider->id, $coupon->provider_profile_id);

        $this->actingAs($provider->user)->post("/provider/coupons/{$coupon->id}/toggle-active")->assertRedirect();
        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_a_provider_cannot_reuse_a_code_they_already_have(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Coupon::factory()->for($provider, 'providerProfile')->create(['code' => 'SAVE10']);

        $this->actingAs($provider->user)->post('/provider/coupons', [
            'code' => 'SAVE10', 'type' => 'flat', 'value' => 100,
        ])->assertSessionHasErrors('code');
    }

    public function test_two_different_providers_can_each_use_the_same_code(): void
    {
        $providerA = ProviderProfile::factory()->sellsProducts()->create();
        $providerB = ProviderProfile::factory()->sellsProducts()->create();
        Coupon::factory()->for($providerA, 'providerProfile')->create(['code' => 'SAVE10']);

        $this->actingAs($providerB->user)->post('/provider/coupons', [
            'code' => 'SAVE10', 'type' => 'flat', 'value' => 100,
        ])->assertRedirect();

        $this->assertSame(2, Coupon::where('code', 'SAVE10')->count());
    }

    // ─── Applying a coupon in the cart ──────────────────────────────

    public function test_consumer_can_apply_a_valid_coupon_to_their_cart(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->for($provider, 'providerProfile')->percentage(10)->create(['code' => 'SAVE10']);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($consumer)->post('/cart/coupon', [
            'provider_profile_id' => $provider->id, 'code' => 'save10',
        ])->assertRedirect();

        $this->actingAs($consumer)->get('/cart')->assertOk();
        $this->assertSame('SAVE10', session('cart_coupons.' . $provider->id));
    }

    public function test_an_expired_or_unknown_coupon_is_rejected(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        Coupon::factory()->for($provider, 'providerProfile')->expired()->create(['code' => 'OLDCODE']);

        $this->actingAs($consumer)->post('/cart/coupon', [
            'provider_profile_id' => $provider->id, 'code' => 'OLDCODE',
        ])->assertRedirect();
        $this->assertNull(session('cart_coupons.' . $provider->id));

        $this->actingAs($consumer)->post('/cart/coupon', [
            'provider_profile_id' => $provider->id, 'code' => 'NOSUCHCODE',
        ])->assertRedirect();
        $this->assertNull(session('cart_coupons.' . $provider->id));
    }

    // ─── Full checkout with a coupon ────────────────────────────────

    public function test_checkout_applies_the_coupon_discount_and_records_a_one_time_redemption(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create(['shipping_type' => 'flat', 'shipping_flat_rate' => 100]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->for($provider, 'providerProfile')->percentage(10)->create(['code' => 'SAVE10']);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/cart/coupon', ['provider_profile_id' => $provider->id, 'code' => 'SAVE10']);

        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('100.00', (string) $order->discount_amount); // 10% of 1000
        $this->assertSame('900.00', (string) $order->total_amount); // pickup => no shipping, 1000 - 100

        $this->assertSame(1, CouponRedemption::where('coupon_id', $coupon->id)->where('user_id', $consumer->id)->count());
        $this->assertNull(session('cart_coupons.' . $provider->id)); // cleared after use
    }

    public function test_a_coupon_cannot_be_used_twice_by_the_same_user(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->for($provider, 'providerProfile')->flat(50)->create(['code' => 'ONCE']);

        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'user_id' => $consumer->id,
            'order_id' => Order::factory()->for($provider, 'providerProfile')->create()->id,
            'discount_amount' => 50,
        ]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/cart/coupon', ['provider_profile_id' => $provider->id, 'code' => 'ONCE']);

        // Applying it failed silently (session never set) — checkout proceeds at full price, no discount.
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ])->assertRedirect();

        $order = Order::where('provider_profile_id', $provider->id)->latest()->firstOrFail();
        $this->assertNull($order->coupon_id);
        $this->assertSame('0.00', (string) $order->discount_amount);
    }

    public function test_cancelling_an_order_frees_the_coupon_for_reuse(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 10]);
        Coupon::factory()->for($provider, 'providerProfile')->flat(50)->create(['code' => 'FREEBIE']);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/cart/coupon', ['provider_profile_id' => $provider->id, 'code' => 'FREEBIE']);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ]);

        $order = Order::firstOrFail();
        $this->assertSame(1, CouponRedemption::count());

        $this->actingAs($consumer)->post("/orders/{$order->id}/cancel");
        $this->assertSame(0, CouponRedemption::count());
    }
}
