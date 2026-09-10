<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    private function completedOrderWithItem(): array
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);

        $order = Order::factory()->for($provider, 'providerProfile')->create([
            'consumer_id' => $consumer->id,
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now()->subDays(8),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ]);

        return [$consumer, $order, $product];
    }

    public function test_consumer_can_rate_a_product_from_a_completed_order(): void
    {
        [$consumer, $order, $product] = $this->completedOrderWithItem();

        $this->actingAs($consumer)
            ->post(route('consumer.orders.reviews.store', $order), ['product_id' => $product->id, 'rating' => 5, 'comment' => 'Great!'])
            ->assertRedirect(route('consumer.orders.show', $order));

        $this->assertDatabaseHas('product_reviews', [
            'order_id' => $order->id, 'product_id' => $product->id, 'user_id' => $consumer->id, 'rating' => 5,
        ]);

        $this->assertSame(5.0, $product->fresh()->ratingAvg());
        $this->assertSame(1, $product->fresh()->reviewsCount());

        $this->get(route('shop.show', $product))->assertOk()->assertSee('Great!');
    }

    public function test_a_product_cannot_be_reviewed_twice_on_the_same_order(): void
    {
        [$consumer, $order, $product] = $this->completedOrderWithItem();

        $this->actingAs($consumer)->post(route('consumer.orders.reviews.store', $order), ['product_id' => $product->id, 'rating' => 4]);
        $this->actingAs($consumer)->post(route('consumer.orders.reviews.store', $order), ['product_id' => $product->id, 'rating' => 2])
            ->assertStatus(422);

        $this->assertSame(1, ProductReview::count());
    }

    public function test_another_consumer_cannot_review_someone_elses_order(): void
    {
        [, $order, $product] = $this->completedOrderWithItem();
        $intruder = User::factory()->create(['role' => User::ROLE_CONSUMER]);

        $this->actingAs($intruder)
            ->post(route('consumer.orders.reviews.store', $order), ['product_id' => $product->id, 'rating' => 5])
            ->assertForbidden();
    }

    public function test_a_pending_orders_products_cannot_be_reviewed(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $order = Order::factory()->for($provider, 'providerProfile')->create(['consumer_id' => $consumer->id, 'status' => Order::STATUS_PENDING]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

        $this->actingAs($consumer)
            ->post(route('consumer.orders.reviews.store', $order), ['product_id' => $product->id, 'rating' => 5])
            ->assertStatus(422);
    }

    public function test_review_reminder_command_notifies_and_marks_orders_after_seven_days(): void
    {
        [$consumer, $order] = $this->completedOrderWithItem();

        Artisan::call('products:remind-reviews');

        $this->assertNotNull($order->fresh()->review_reminder_sent_at);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $consumer->id, 'type' => 'product_review_reminder']);
    }

    public function test_review_reminder_command_does_not_fire_twice(): void
    {
        [, $order] = $this->completedOrderWithItem();

        Artisan::call('products:remind-reviews');
        $firstSentAt = $order->fresh()->review_reminder_sent_at;

        Artisan::call('products:remind-reviews');

        $this->assertSame(1, Notification::where('type', 'product_review_reminder')->count());
        $this->assertEquals($firstSentAt, $order->fresh()->review_reminder_sent_at);
    }

    public function test_review_reminder_command_skips_orders_completed_less_than_seven_days_ago(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $order = Order::factory()->for($provider, 'providerProfile')->create([
            'consumer_id' => $consumer->id, 'status' => Order::STATUS_COMPLETED, 'completed_at' => now()->subDays(2),
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100]);

        Artisan::call('products:remind-reviews');

        $this->assertNull($order->fresh()->review_reminder_sent_at);
        $this->assertDatabaseMissing('app_notifications', ['user_id' => $consumer->id, 'type' => 'product_review_reminder']);
    }
}
