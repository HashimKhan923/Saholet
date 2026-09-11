<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderOrdersSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_search_orders_by_customer_email_and_product_name(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $match = User::create([
            'name' => 'Ayesha Khan', 'email' => 'ayesha@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        $other = User::create([
            'name' => 'Bilal Ahmed', 'email' => 'bilal@example.com', 'phone' => '+923003334444',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $matchOrder = Order::create([
            'reference' => 'ORD-MATCH1', 'consumer_id' => $match->id, 'provider_profile_id' => $provider->id,
            'fulfillment_method' => 'pickup', 'payment_method' => 'cash', 'status' => 'pending',
            'subtotal' => 1000, 'shipping_amount' => 0, 'total_amount' => 1000,
            'commission_rate' => 10, 'commission_amount' => 100, 'provider_amount' => 900,
        ]);
        OrderItem::create(['order_id' => $matchOrder->id, 'product_name' => 'Pipe Wrench', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        $otherOrder = Order::create([
            'reference' => 'ORD-OTHER1', 'consumer_id' => $other->id, 'provider_profile_id' => $provider->id,
            'fulfillment_method' => 'pickup', 'payment_method' => 'cash', 'status' => 'pending',
            'subtotal' => 500, 'shipping_amount' => 0, 'total_amount' => 500,
            'commission_rate' => 10, 'commission_amount' => 50, 'provider_amount' => 450,
        ]);
        OrderItem::create(['order_id' => $otherOrder->id, 'product_name' => 'Screwdriver Set', 'unit_price' => 500, 'quantity' => 1, 'line_total' => 500]);

        // Search by customer email.
        $response = $this->actingAs($provider->user)->get('/provider/orders?q=ayesha@example.com');
        $response->assertOk();
        $response->assertSee('ORD-MATCH1');
        $response->assertDontSee('ORD-OTHER1');

        // Search by product name.
        $response = $this->actingAs($provider->user)->get('/provider/orders?q=Wrench');
        $response->assertOk();
        $response->assertSee('ORD-MATCH1');
        $response->assertDontSee('ORD-OTHER1');
    }
}
