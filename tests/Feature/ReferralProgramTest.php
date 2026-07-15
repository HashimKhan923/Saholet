<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ReferralReward;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_with_a_referral_code_links_the_referrer(): void
    {
        $referrer = User::create([
            'name' => 'Referrer', 'email' => 'referrer@example.com', 'phone' => '+923003333333',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
            'referral_code' => 'REFCODE1',
        ]);

        $this->post('/register', [
            'name' => 'New Consumer',
            'email' => 'newconsumer@example.com',
            'phone' => '+923004444444',
            'role' => 'consumer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'refcode1',
        ])->assertRedirect();

        $newUser = User::where('email', 'newconsumer@example.com')->firstOrFail();
        $this->assertSame($referrer->id, $newUser->referred_by);
        $this->assertNotNull($newUser->referral_code);
    }

    public function test_referred_users_first_payment_credits_both_sides_exactly_once(): void
    {
        $category = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Pipe Fix', 'slug' => 'pipe-fix',
            'base_price' => 1500, 'duration_minutes' => 45, 'is_active' => true,
        ]);

        $providerUser = User::create([
            'name' => 'Provider', 'email' => 'prov@example.com', 'phone' => '+923005555555',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Prov Co', 'city' => 'Lahore',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 1800, 'is_active' => true]);

        $referrer = User::create([
            'name' => 'Referrer', 'email' => 'referrer2@example.com', 'phone' => '+923006666666',
            'role' => User::ROLE_CONSUMER, 'password' => 'password', 'referral_code' => 'REFCODE2',
        ]);
        $consumer = User::create([
            'name' => 'Referred Consumer', 'email' => 'referred@example.com', 'phone' => '+923007777777',
            'role' => User::ROLE_CONSUMER, 'password' => 'password', 'referred_by' => $referrer->id,
        ]);

        $this->actingAs($consumer)->post("/bookings/create/{$profile->id}/{$service->slug}", [
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '11:00',
            'address' => 'Some Address, Lahore',
        ])->assertRedirect();

        $booking = Booking::firstOrFail();

        $this->actingAs($consumer)
            ->post("/bookings/{$booking->id}/pay", ['gateway' => 'mock'])
            ->assertRedirect("/bookings/{$booking->id}");

        $this->assertSame(1, ReferralReward::count());

        $reward = ReferralReward::firstOrFail();
        $this->assertSame($referrer->id, $reward->referrer_id);
        $this->assertSame($consumer->id, $reward->referred_user_id);

        $this->assertSame(
            number_format((float) config('referrals.referrer_reward'), 2, '.', ''),
            (string) $referrer->fresh()->credit_balance
        );
        $this->assertSame(
            number_format((float) config('referrals.referred_reward'), 2, '.', ''),
            (string) $consumer->fresh()->credit_balance
        );
    }
}
