<?php

namespace Tests\Feature;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function consumer(): User
    {
        return User::create([
            'name' => 'Shopper', 'email' => 'shopper' . uniqid() . '@example.com', 'phone' => '+92300' . random_int(1000000, 9999999),
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'shopadmin' . uniqid() . '@example.com', 'phone' => '+92300' . random_int(1000000, 9999999),
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
    }

    // ─── Cart ───────────────────────────────────────────────────────

    public function test_consumer_can_add_update_and_remove_cart_items(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['stock_quantity' => 10, 'price' => 500]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect();

        $item = $consumer->cart()->first()->items()->firstOrFail();
        $this->assertSame(2, $item->quantity);

        $this->actingAs($consumer)->put("/cart/items/{$item->id}", ['quantity' => 5])->assertRedirect();
        $this->assertSame(5, $item->fresh()->quantity);

        $this->actingAs($consumer)->delete("/cart/items/{$item->id}")->assertRedirect();
        $this->assertSame(0, $consumer->cart->items()->count());
    }

    public function test_cart_quantity_is_capped_at_available_stock(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['stock_quantity' => 3]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 10])->assertRedirect();

        $this->assertSame(3, $consumer->cart->items()->firstOrFail()->quantity);
    }

    public function test_a_discounted_products_sale_price_is_what_actually_gets_charged(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create([
            'price' => 1000, 'discount_price' => 800, 'stock_quantity' => 5,
        ]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertSame('1600.00', number_format($consumer->cart->items()->first()->lineTotal(), 2, '.', ''));

        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ]);

        $order = Order::firstOrFail();
        $this->assertSame('1600.00', (string) $order->subtotal); // 2 x 800, not 2 x 1000
        $item = $order->items()->firstOrFail();
        $this->assertSame('800.00', (string) $item->unit_price);
    }

    // ─── Checkout: multi-provider split ────────────────────────────

    public function test_checkout_splits_a_mixed_cart_into_one_order_per_provider(): void
    {
        $consumer = $this->consumer();
        $address = $consumer->addresses()->create(['label' => 'Home', 'address' => '1 Main St', 'city' => 'Karachi']);

        $providerA = ProviderProfile::factory()->sellsProducts()->create();
        $providerB = ProviderProfile::factory()->sellsProducts()->create();
        $productA = Product::factory()->for($providerA, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 5]);
        $productB = Product::factory()->for($providerB, 'providerProfile')->create(['price' => 2000, 'stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $productA->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $productB->id, 'quantity' => 1]);

        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [
                ['provider_profile_id' => $providerA->id, 'fulfillment_method' => 'delivery', 'payment_method' => 'cash', 'address_id' => $address->id],
                ['provider_profile_id' => $providerB->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash'],
            ],
        ])->assertRedirect(route('consumer.orders.index'));

        $this->assertSame(2, Order::count());
        $this->assertSame(0, $consumer->cart->items()->count());

        $orderA = Order::where('provider_profile_id', $providerA->id)->firstOrFail();
        $this->assertSame('delivery', $orderA->fulfillment_method);
        $this->assertSame('1150.00', (string) $orderA->total_amount); // 1000 + 150 flat shipping

        $orderB = Order::where('provider_profile_id', $providerB->id)->firstOrFail();
        $this->assertSame('pickup', $orderB->fulfillment_method);
        $this->assertSame('2000.00', (string) $orderB->total_amount); // no shipping
    }

    public function test_checkout_rejects_a_fulfillment_method_the_provider_does_not_offer(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->create(); // no shipping_type, pickup disabled
        $product = Product::factory()->for($provider, 'providerProfile')->create(['stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [
                ['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash'],
            ],
        ])->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_blocks_ordering_more_than_available_stock(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['stock_quantity' => 2]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        // Someone else buys stock down to 1 between add-to-cart and checkout.
        $product->update(['stock_quantity' => 1]);

        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [
                ['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash'],
            ],
        ])->assertStatus(422);

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    // ─── Full flow: pickup + cash ──────────────────────────────────

    public function test_pickup_cash_flow_settles_commission_as_a_debt_on_completion(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create(['product_commission_rate' => 10]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ]);

        $order = Order::firstOrFail();
        $this->assertSame(3, $product->fresh()->stock_quantity); // decremented at checkout
        $this->assertSame(0, Payment::count()); // no payment yet — cash settles on completion

        $providerUser = $provider->user;
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/confirm")->assertRedirect();
        $this->assertSame('confirmed', $order->fresh()->status);

        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/ready")->assertRedirect();
        $this->assertSame('ready', $order->fresh()->status);

        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/complete")->assertRedirect();
        $order->refresh();
        $this->assertSame('completed', $order->status);

        // The provider's completion action settles the cash payment inline — no separate consumer step.
        $payment = Payment::firstOrFail();
        $this->assertSame('2000.00', (string) $payment->amount);

        $wallet = Wallet::where('user_id', $providerUser->id)->firstOrFail();
        $this->assertSame('-200.00', (string) $wallet->available_balance); // 10% of 2000 owed as debt
        $this->assertDatabaseHas('ledger_entries', [
            'wallet_id' => $wallet->id, 'payment_id' => $payment->id, 'type' => 'cash_commission_due',
        ]);
    }

    /**
     * Regression: production uses real SMTP, so a mail-server hiccup while emailing the
     * invoice can throw — that call used to be unguarded inside the same DB::transaction()
     * that marks the order completed and charges commission, so the exception rolled back
     * the whole completion (order silently stuck on "ready") and surfaced as a 500 to the
     * provider. The invoice email must be best-effort, matching Notifier's "never throws
     * into the caller" contract.
     */
    public function test_completing_a_cash_order_survives_an_invoice_email_failure(): void
    {
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('SMTP connection failed'));

        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create(['product_commission_rate' => 10]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ]);

        $order = Order::firstOrFail();
        $providerUser = $provider->user;
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/confirm")->assertRedirect();
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/ready")->assertRedirect();

        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/complete")->assertRedirect();

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertSame(1, Payment::count());
    }

    // ─── Full flow: delivery + bank transfer ───────────────────────

    public function test_delivery_bank_transfer_flow_escrows_then_releases_on_completion(): void
    {
        Storage::fake('public');

        $consumer = $this->consumer();
        $address = $consumer->addresses()->create(['label' => 'Home', 'address' => '1 Main St', 'city' => 'Karachi']);
        $provider = ProviderProfile::factory()->sellsProducts()->create(['product_commission_rate' => 8]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 3000, 'stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [[
                'provider_profile_id' => $provider->id,
                'fulfillment_method' => 'delivery',
                'payment_method' => 'bank_transfer',
                'address_id' => $address->id,
                'screenshot' => UploadedFile::fake()->image('proof.jpg'),
            ]],
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();
        $this->assertTrue($payment->isPending());
        $this->assertNotNull($payment->screenshot_path);

        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/payments/{$payment->id}/verify")->assertRedirect();
        $this->assertTrue($payment->fresh()->isEscrow());

        $providerUser = $provider->user;
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/confirm");
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/ready", [
            'delivery_method' => 'Sent via Bykea rider, arriving by 5pm',
        ])->assertRedirect();
        $this->assertSame('Sent via Bykea rider, arriving by 5pm', $order->fresh()->delivery_method);

        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/complete")->assertRedirect();

        $payment->refresh();
        $this->assertTrue($payment->isReleased());
        $this->assertSame('2898.00', (string) $payment->provider_amount); // (3000 + 150 shipping) - 8% commission

        $wallet = Wallet::where('user_id', $providerUser->id)->firstOrFail();
        $this->assertSame($payment->provider_amount, (string) $wallet->available_balance);
    }

    public function test_bank_transfer_verified_after_order_already_completed_releases_immediately(): void
    {
        // Self-pickup can be fulfilled by the provider before an admin gets
        // around to checking the bank statement — verifying afterwards must
        // still release the money, not leave it stuck in escrow forever.
        Storage::fake('public');

        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create(['product_commission_rate' => 8]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 3000, 'stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [[
                'provider_profile_id' => $provider->id,
                'fulfillment_method' => 'pickup',
                'payment_method' => 'bank_transfer',
                'screenshot' => UploadedFile::fake()->image('proof.jpg'),
            ]],
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        $providerUser = $provider->user;
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/confirm");
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/ready");
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/complete")->assertRedirect();

        // Completion ran before verification — nothing to release yet, payment is still pending.
        $this->assertTrue($payment->fresh()->isPending());

        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/payments/{$payment->id}/verify")->assertRedirect();

        $payment->refresh();
        $this->assertTrue($payment->isReleased());

        $wallet = Wallet::where('user_id', $providerUser->id)->firstOrFail();
        $this->assertSame($payment->provider_amount, (string) $wallet->available_balance);
    }

    // ─── Cancellation ───────────────────────────────────────────────

    public function test_cancelling_a_pending_order_restores_stock(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 500, 'stock_quantity' => 10]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 3]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ]);

        $this->assertSame(7, $product->fresh()->stock_quantity);

        $order = Order::firstOrFail();
        $this->actingAs($consumer)->post("/orders/{$order->id}/cancel", ['reason' => 'Changed my mind'])->assertRedirect();

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('Changed my mind', $order->cancel_reason);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_a_delivery_order_can_be_marked_ready_with_no_tracking_info_at_all(): void
    {
        $consumer = $this->consumer();
        $address = $consumer->addresses()->create(['label' => 'Home', 'address' => '1 Main St', 'city' => 'Karachi']);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'delivery', 'payment_method' => 'cash', 'address_id' => $address->id]],
        ]);

        $order = Order::firstOrFail();
        $providerUser = $provider->user;
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/confirm");

        // No delivery_method at all — most local delivery is a rider or the provider themselves, not a trackable courier.
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/ready")->assertRedirect();

        $order->refresh();
        $this->assertSame('ready', $order->status);
        $this->assertNull($order->delivery_method);
    }

    public function test_a_ready_order_can_no_longer_be_cancelled(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create(['stock_quantity' => 5]);

        $this->actingAs($consumer)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($consumer)->post('/checkout', [
            'orders' => [['provider_profile_id' => $provider->id, 'fulfillment_method' => 'pickup', 'payment_method' => 'cash']],
        ]);

        $order = Order::firstOrFail();
        $providerUser = $provider->user;
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/confirm");
        $this->actingAs($providerUser)->post("/provider/orders/{$order->id}/ready");

        $this->actingAs($consumer)->post("/orders/{$order->id}/cancel")->assertStatus(422);
    }
}
