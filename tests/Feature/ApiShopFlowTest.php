<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The whole shop feature (browse, wishlist, cart, checkout, fulfillment,
 * reviews) has thorough web-route coverage in ShopCheckoutTest and
 * ProductReviewTest, which exercises the shared business logic. This
 * confirms the /api/* mirrors the mobile app will actually call are wired
 * correctly end-to-end — routes, auth, JSON shapes — not re-testing every
 * business rule already covered on the web side.
 */
class ApiShopFlowTest extends TestCase
{
    use RefreshDatabase;

    private function consumer(): User
    {
        return User::factory()->create(['role' => User::ROLE_CONSUMER]);
    }

    public function test_full_cash_purchase_flow_via_api(): void
    {
        $consumer = $this->consumer();
        $provider = ProviderProfile::factory()->sellsProducts()->create(['product_commission_rate' => 8]);
        $product = Product::factory()->for($provider, 'providerProfile')->create(['price' => 1000, 'stock_quantity' => 10]);

        // Public browse — no auth needed.
        $this->getJson('/api/shop/products')
            ->assertOk()
            ->assertJsonPath('products.0.id', $product->id);

        $this->getJson("/api/shop/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('product.name', $product->name)
            ->assertJsonPath('product.rating_avg', null)
            ->assertJsonPath('product.reviews_count', 0);

        Sanctum::actingAs($consumer);

        // Wishlist toggle + index.
        $this->postJson('/api/consumer/wishlist/toggle', ['product_id' => $product->id])
            ->assertCreated()
            ->assertJsonPath('wishlisted', true);
        $this->getJson('/api/consumer/wishlist')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1);

        // Cart.
        $this->postJson('/api/consumer/cart/items', ['product_id' => $product->id, 'quantity' => 2])
            ->assertCreated();
        $cart = $this->getJson('/api/consumer/cart')->assertOk();
        $cart->assertJsonPath('groups.0.subtotal', 2000);

        // Checkout — pickup + cash, no address needed.
        $checkout = $this->postJson('/api/consumer/checkout', [
            'orders' => [[
                'provider_profile_id' => $provider->id,
                'fulfillment_method' => 'pickup',
                'payment_method' => 'cash',
            ]],
        ])->assertCreated();

        $order = Order::firstOrFail();
        $this->assertSame($order->reference, $checkout->json('orders.0.reference'));

        // Consumer order views.
        $this->getJson('/api/consumer/orders')->assertOk()->assertJsonPath('pagination.total', 1);
        $this->getJson("/api/consumer/orders/{$order->id}")->assertOk()->assertJsonPath('order.status', 'pending');

        // Provider fulfillment.
        Sanctum::actingAs($provider->user);
        $this->postJson("/api/provider/orders/{$order->id}/confirm")->assertOk();
        $this->postJson("/api/provider/orders/{$order->id}/ready")->assertOk();
        $this->postJson("/api/provider/orders/{$order->id}/complete")->assertOk()
            ->assertJsonPath('order.status', 'completed');

        // Stock decremented.
        $this->assertSame(8, $product->fresh()->stock_quantity);

        // Review, now that the order is completed.
        Sanctum::actingAs($consumer);
        $this->postJson("/api/consumer/orders/{$order->id}/reviews", [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ])->assertCreated();

        $this->getJson("/api/shop/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('product.rating_avg', 5)
            ->assertJsonPath('product.reviews_count', 1);
    }

    public function test_wishlist_toggle_removes_on_second_call(): void
    {
        $consumer = $this->consumer();
        $product = Product::factory()->create();
        Sanctum::actingAs($consumer);

        $this->postJson('/api/consumer/wishlist/toggle', ['product_id' => $product->id])
            ->assertCreated()->assertJsonPath('wishlisted', true);

        $this->postJson('/api/consumer/wishlist/toggle', ['product_id' => $product->id])
            ->assertOk()->assertJsonPath('wishlisted', false);
    }

    public function test_checkout_requires_authentication(): void
    {
        $this->postJson('/api/consumer/checkout', ['orders' => []])->assertUnauthorized();
    }
}
