<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    private function makePlan(int $frequencyMonths = 6, ?int $totalVisits = 2): array
    {
        $category = Category::create(['name' => 'HVAC', 'slug' => 'hvac', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'AC Servicing', 'slug' => 'ac-servicing',
            'base_price' => 3000, 'duration_minutes' => 60, 'is_active' => true,
        ]);
        $plan = SubscriptionPlan::create([
            'service_id' => $service->id, 'name' => 'AC Servicing — Biannual', 'slug' => 'ac-servicing-biannual',
            'frequency_months' => $frequencyMonths, 'total_visits' => $totalVisits, 'price_per_visit' => 2500,
            'is_active' => true, 'sort_order' => 0,
        ]);

        return [$plan, $service];
    }

    public function test_admin_can_create_a_subscription_plan(): void
    {
        [, $service] = $this->makePlan();

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        $this->actingAs($admin)->post('/admin/subscription-plans', [
            'service_id' => $service->id,
            'name' => 'Generator Maintenance — Quarterly',
            'frequency_months' => 3,
            'total_visits' => 4,
            'price_per_visit' => 1800,
            'sort_order' => 0,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_plans', ['name' => 'Generator Maintenance — Quarterly']);
    }

    public function test_consumer_can_subscribe_and_admin_assignment_creates_first_payable_booking(): void
    {
        [$plan, $service] = $this->makePlan();

        $consumer = User::create([
            'name' => 'Consumer', 'email' => 'consumer@example.com', 'phone' => '+923002223333',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        $providerUser = User::create([
            'name' => 'Provider', 'email' => 'provider@example.com', 'phone' => '+923003334444',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Cool Air Co', 'city' => 'Lahore',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 2500, 'is_active' => true]);
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin2@example.com', 'phone' => '+923004445555',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        $this->actingAs($consumer)->post("/subscriptions/{$plan->slug}/subscribe", [
            'address' => '10 Main Street, Lahore',
            'city' => 'Lahore',
            'start_date' => now()->addDay()->toDateString(),
        ])->assertRedirect();

        $subscription = Subscription::firstOrFail();
        $this->assertTrue($subscription->isPendingAssignment());
        $this->assertSame($consumer->id, $subscription->consumer_id);

        $this->actingAs($admin)->post("/admin/subscriptions/{$subscription->id}/assign", [
            'provider_profile_id' => $profile->id,
            'scheduled_time' => '11:00',
        ])->assertRedirect();

        $subscription->refresh();
        $this->assertTrue($subscription->isActive());
        $this->assertSame(1, $subscription->visits_used);
        $this->assertSame($profile->id, $subscription->provider_profile_id);

        $booking = Booking::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertTrue($booking->isPayable());
        $this->assertSame('2500.00', (string) $booking->price);

        // next_visit_date advanced by the plan's frequency
        $this->assertEquals(
            now()->addDay()->addMonths(6)->toDateString(),
            $subscription->next_visit_date->toDateString()
        );
    }

    public function test_generate_due_visit_command_creates_next_booking_and_completes_after_total_visits(): void
    {
        [$plan, $service] = $this->makePlan(frequencyMonths: 1, totalVisits: 2);

        $consumer = User::create([
            'name' => 'Consumer', 'email' => 'consumer3@example.com', 'phone' => '+923005556666',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        $providerUser = User::create([
            'name' => 'Provider', 'email' => 'provider3@example.com', 'phone' => '+923006667777',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Cool Air Co', 'city' => 'Lahore',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 2500, 'is_active' => true]);

        $subscription = Subscription::create([
            'reference' => 'SUB-TESTFIX1', 'subscription_plan_id' => $plan->id, 'consumer_id' => $consumer->id,
            'address' => 'Test address', 'city' => 'Lahore', 'status' => Subscription::STATUS_PENDING_ASSIGNMENT,
            'next_visit_date' => now()->subDay(),
        ]);

        app(SubscriptionService::class)->assignProvider($subscription, $profile, '09:00');
        $subscription->refresh();
        $this->assertSame(1, $subscription->visits_used);
        $this->assertTrue($subscription->isActive());

        // Second (final) visit is now due — run the scheduled command.
        $subscription->update(['next_visit_date' => now()->subDay()]);

        $this->artisan('subscriptions:generate-due-visits')->assertSuccessful();

        $subscription->refresh();
        $this->assertSame(2, $subscription->visits_used);
        $this->assertTrue($subscription->status === Subscription::STATUS_COMPLETED);
        $this->assertSame(2, Booking::where('subscription_id', $subscription->id)->count());

        // No more visits should be generated once completed.
        $subscription->update(['next_visit_date' => now()->subDay(), 'status' => Subscription::STATUS_COMPLETED]);
        $this->artisan('subscriptions:generate-due-visits')->assertSuccessful();
        $this->assertSame(2, Booking::where('subscription_id', $subscription->id)->count());
    }
}
