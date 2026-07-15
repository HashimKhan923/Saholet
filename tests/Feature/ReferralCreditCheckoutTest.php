<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralCreditCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(User $consumer, float $price = 2500): array
    {
        $category = Category::create(['name' => 'AC Repair', 'slug' => 'ac-repair-credit', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'AC Service', 'slug' => 'ac-service-credit',
            'base_price' => 2000, 'duration_minutes' => 60, 'is_active' => true,
        ]);
        $providerUser = User::create([
            'name' => 'Provider', 'email' => 'creditprovider@example.com', 'phone' => '+923001110000',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Cool Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => $price, 'is_active' => true]);

        $this->actingAs($consumer)->post("/bookings/create/{$profile->id}/{$service->slug}", [
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '10:00',
            'address' => 'Test Address, Karachi',
        ])->assertRedirect();

        $booking = Booking::where('consumer_id', $consumer->id)->latest()->firstOrFail();

        return [$booking, $profile];
    }

    public function test_partial_credit_reduces_gateway_charge_but_escrow_stays_at_full_price(): void
    {
        $consumer = User::create([
            'name' => 'Consumer', 'email' => 'partialcredit@example.com', 'phone' => '+923002220000',
            'role' => User::ROLE_CONSUMER, 'password' => 'password', 'credit_balance' => 1000,
        ]);

        [$booking, $profile] = $this->makeBooking($consumer, 2500);

        $this->actingAs($consumer)
            ->post("/bookings/{$booking->id}/pay", ['gateway' => 'mock', 'apply_credit' => '1'])
            ->assertRedirect("/bookings/{$booking->id}");

        $payment = Payment::where('booking_id', $booking->id)->firstOrFail();
        $this->assertTrue($payment->isEscrow());
        $this->assertSame('2500.00', (string) $payment->amount);
        $this->assertSame('1000.00', (string) $payment->credit_applied);
        $this->assertSame('1500.00', number_format($payment->chargeAmount(), 2, '.', ''));

        // Full price still escrowed for the provider — credit doesn't shortchange them.
        $wallet = Wallet::where('user_id', $profile->user_id)->firstOrFail();
        $this->assertSame('2500.00', (string) $wallet->escrow_balance);

        $this->assertSame('0.00', (string) $consumer->fresh()->credit_balance);
    }

    public function test_credit_covering_the_full_price_bypasses_the_gateway_entirely(): void
    {
        $consumer = User::create([
            'name' => 'Consumer', 'email' => 'fullcredit@example.com', 'phone' => '+923003330000',
            'role' => User::ROLE_CONSUMER, 'password' => 'password', 'credit_balance' => 5000,
        ]);

        [$booking, $profile] = $this->makeBooking($consumer, 2500);

        $this->actingAs($consumer)
            ->post("/bookings/{$booking->id}/pay", ['apply_credit' => '1'])
            ->assertRedirect("/bookings/{$booking->id}");

        $payment = Payment::where('booking_id', $booking->id)->firstOrFail();
        $this->assertTrue($payment->isEscrow());
        $this->assertSame('credit', $payment->gateway);
        $this->assertSame('2500.00', (string) $payment->amount);
        $this->assertSame('2500.00', (string) $payment->credit_applied);
        $this->assertTrue($payment->isFullyCoveredByCredit());

        $wallet = Wallet::where('user_id', $profile->user_id)->firstOrFail();
        $this->assertSame('2500.00', (string) $wallet->escrow_balance);

        // Only the amount actually applied is debited — remaining balance preserved.
        $this->assertSame('2500.00', (string) $consumer->fresh()->credit_balance);
    }

    public function test_credit_is_not_debited_if_payment_is_never_finalized(): void
    {
        $consumer = User::create([
            'name' => 'Consumer', 'email' => 'unusedcredit@example.com', 'phone' => '+923004440000',
            'role' => User::ROLE_CONSUMER, 'password' => 'password', 'credit_balance' => 500,
        ]);

        [$booking] = $this->makeBooking($consumer, 2500);

        // Visiting the payment page (not submitting) must never touch the balance.
        $this->actingAs($consumer)->get("/bookings/{$booking->id}/pay")->assertOk();

        $this->assertSame('500.00', (string) $consumer->fresh()->credit_balance);
        $this->assertSame(0, Payment::where('booking_id', $booking->id)->count());
    }
}
