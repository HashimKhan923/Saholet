<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\CorporateAccount;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_company_account_and_invite_an_existing_consumer(): void
    {
        $owner = User::create([
            'name' => 'Owner', 'email' => 'owner@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        $teammate = User::create([
            'name' => 'Teammate', 'email' => 'teammate@example.com', 'phone' => '+923002223333',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $this->actingAs($owner)->post('/company', [
            'name' => 'Acme Corp',
            'billing_email' => 'billing@acme.com',
        ])->assertRedirect();

        $account = CorporateAccount::firstOrFail();
        $owner->refresh();
        $this->assertSame($account->id, $owner->corporate_account_id);
        $this->assertSame(CorporateAccount::ROLE_OWNER, $owner->corporate_role);

        $this->actingAs($owner)->post('/company/members', [
            'email' => 'teammate@example.com',
        ])->assertRedirect();

        $teammate->refresh();
        $this->assertSame($account->id, $teammate->corporate_account_id);
        $this->assertSame(CorporateAccount::ROLE_MEMBER, $teammate->corporate_role);
    }

    public function test_a_members_booking_rolls_up_into_the_corporate_accounts_spend(): void
    {
        $category = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Office Cleaning', 'slug' => 'office-cleaning',
            'base_price' => 4000, 'duration_minutes' => 90, 'is_active' => true,
        ]);
        $providerUser = User::create([
            'name' => 'Provider', 'email' => 'provider@example.com', 'phone' => '+923003334444',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Clean Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 4500, 'is_active' => true]);

        $owner = User::create([
            'name' => 'Owner', 'email' => 'owner2@example.com', 'phone' => '+923004445555',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        $account = CorporateAccount::create([
            'name' => 'Acme Corp', 'owner_id' => $owner->id, 'billing_email' => 'billing@acme.com',
        ]);
        $owner->update(['corporate_account_id' => $account->id, 'corporate_role' => CorporateAccount::ROLE_OWNER]);

        $member = User::create([
            'name' => 'Member', 'email' => 'member@example.com', 'phone' => '+923005556666',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
            'corporate_account_id' => $account->id, 'corporate_role' => CorporateAccount::ROLE_MEMBER,
        ]);

        $this->actingAs($member)->post("/bookings/create/{$profile->id}/{$service->slug}", [
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '10:00',
            'address' => 'Office 4B, Karachi',
        ])->assertRedirect();

        $booking = Booking::where('consumer_id', $member->id)->firstOrFail();
        $this->assertSame($account->id, $booking->corporate_account_id);

        $this->actingAs($member)
            ->post("/bookings/{$booking->id}/pay", ['gateway' => 'mock'])
            ->assertRedirect("/bookings/{$booking->id}");

        $account->refresh();
        $this->assertSame(4500.0, $account->totalSpend());

        $this->actingAs($owner)->get('/company/dashboard')->assertOk()->assertSee('4,500');
    }

    public function test_owner_cannot_remove_themselves_and_can_remove_a_member(): void
    {
        $owner = User::create([
            'name' => 'Owner', 'email' => 'owner3@example.com', 'phone' => '+923006667777',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        $account = CorporateAccount::create([
            'name' => 'Acme Corp', 'owner_id' => $owner->id, 'billing_email' => 'billing@acme.com',
        ]);
        $owner->update(['corporate_account_id' => $account->id, 'corporate_role' => CorporateAccount::ROLE_OWNER]);

        $member = User::create([
            'name' => 'Member', 'email' => 'member2@example.com', 'phone' => '+923007778888',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
            'corporate_account_id' => $account->id, 'corporate_role' => CorporateAccount::ROLE_MEMBER,
        ]);

        $this->actingAs($owner)->delete("/company/members/{$owner->id}")->assertForbidden();

        $this->actingAs($owner)->delete("/company/members/{$member->id}")->assertRedirect();
        $member->refresh();
        $this->assertNull($member->corporate_account_id);
    }
}
