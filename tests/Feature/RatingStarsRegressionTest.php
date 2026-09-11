<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: <x-rating-stars> only ever accepted a `:product` prop
 * (reviewsCount()/ratingAvg()), but nearly every call site across the app
 * actually passes raw `:rating`/`:count` scalars (a provider's aggregate
 * rating, or a single booking review's stars) — undetected until a provider
 * with a real reviews_count > 0 was actually viewed, which threw "Undefined
 * variable $product" and 500'd the page. Fixed by making the component
 * accept both calling conventions; these tests hit every affected page with
 * data that has a non-zero rating, so the bug can't quietly regress.
 */
class RatingStarsRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_provider_profile_renders_with_a_real_rating(): void
    {
        $provider = ProviderProfile::factory()->create(['rating_avg' => 4.3, 'reviews_count' => 12]);

        $this->get(route('providers.show', $provider))->assertOk();
    }

    public function test_admin_providers_index_renders_with_a_real_rating(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'rating-admin@example.com', 'phone' => '+923001117777',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        ProviderProfile::factory()->create(['rating_avg' => 4.5, 'reviews_count' => 3]);

        $this->actingAs($admin)->get('/admin/providers')->assertOk();
    }

    public function test_booking_review_stars_render_on_consumer_provider_and_admin_pages(): void
    {
        $provider = ProviderProfile::factory()->create();
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);
        $category = Category::create(['name' => 'Rating Test Category', 'slug' => 'rating-test-category', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Rating Test Service', 'slug' => 'rating-test-service',
            'base_price' => 1500, 'duration_minutes' => 60, 'is_active' => true,
        ]);

        $booking = Booking::create([
            'reference' => 'BK-RATING1', 'consumer_id' => $consumer->id, 'provider_profile_id' => $provider->id,
            'service_id' => $service->id, 'address' => 'Test Street, Karachi', 'scheduled_date' => now()->subDay(),
            'scheduled_time' => '10:00', 'duration_minutes' => 60, 'price' => 1500, 'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::create([
            'booking_id' => $booking->id,
            'provider_profile_id' => $provider->id,
            'service_id' => $service->id,
            'consumer_id' => $consumer->id,
            'rating' => 5,
            'comment' => 'Great job!',
        ]);

        $this->actingAs($consumer)->get('/bookings')->assertOk();
        $this->actingAs($consumer)->get("/bookings/{$booking->id}")->assertOk();
        $this->actingAs($provider->user)->get('/provider/bookings')->assertOk();
        $this->actingAs($provider->user)->get("/provider/bookings/{$booking->id}")->assertOk();
    }
}
