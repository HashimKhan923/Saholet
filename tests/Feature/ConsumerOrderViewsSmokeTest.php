<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Smoke tests only — confirms each new Blade view renders without error. */
class ConsumerOrderViewsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_orders_index_and_show_render_for_every_status(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        foreach (['pending', 'confirmed', 'ready', 'completed', 'cancelled'] as $status) {
            $order = Order::factory()
                ->for($consumer, 'consumer')
                ->for($provider, 'providerProfile')
                ->status($status)
                ->create(['cancel_reason' => $status === 'cancelled' ? 'Changed my mind' : null]);
            $order->items()->create(['product_name' => 'Test item', 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

            $this->actingAs($consumer)->get("/orders/{$order->id}")->assertOk();
        }

        $this->actingAs($consumer)->get('/orders')->assertOk();
    }

    public function test_pickup_order_show_renders(): void
    {
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $order = Order::factory()->for($consumer, 'consumer')->for($provider, 'providerProfile')->pickup()->status('ready')->create();
        $order->items()->create(['product_name' => 'Test item', 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

        $this->actingAs($consumer)->get("/orders/{$order->id}")->assertOk();
    }
}
