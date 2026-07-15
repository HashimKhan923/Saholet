<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPaymentEscrowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paying_for_a_booking_holds_the_full_amount_in_the_providers_escrow(): void
    {
        $category = Category::create(['name' => 'AC Repair', 'slug' => 'ac-repair', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'AC Service',
            'slug' => 'ac-service',
            'base_price' => 2000,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $providerUser = User::create([
            'name' => 'Test Provider', 'email' => 'provider@example.com', 'phone' => '+923001111111',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Test Provider Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 2500, 'is_active' => true]);

        $consumer = User::create([
            'name' => 'Test Consumer', 'email' => 'consumer@example.com', 'phone' => '+923002222222',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $this->actingAs($consumer)->post("/bookings/create/{$profile->id}/{$service->slug}", [
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '10:00',
            'address' => 'Test Address, Karachi',
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame($consumer->id, $booking->consumer_id);
        $this->assertTrue($booking->isPayable());

        $this->actingAs($consumer)
            ->post("/bookings/{$booking->id}/pay", ['gateway' => 'mock'])
            ->assertRedirect("/bookings/{$booking->id}");

        $payment = Payment::where('booking_id', $booking->id)->firstOrFail();
        $this->assertTrue($payment->isEscrow());
        $this->assertSame('2500.00', (string) $payment->amount);
        $this->assertNotNull($payment->gateway_reference);

        $wallet = Wallet::where('user_id', $providerUser->id)->firstOrFail();
        $this->assertSame('2500.00', (string) $wallet->escrow_balance);

        $this->assertDatabaseHas('ledger_entries', [
            'wallet_id' => $wallet->id,
            'payment_id' => $payment->id,
            'bucket' => LedgerEntry::BUCKET_ESCROW,
            'type' => 'hold',
        ]);
    }
}
