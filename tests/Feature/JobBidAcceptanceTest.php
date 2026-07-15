<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Category;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobBidAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_winning_provider_does_not_see_the_taken_by_another_provider_banner(): void
    {
        $category = Category::create(['name' => 'Electrical', 'slug' => 'electrical-jb', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Wiring', 'slug' => 'wiring-jb',
            'base_price' => 3000, 'duration_minutes' => 60, 'is_active' => true,
        ]);

        $consumer = User::create([
            'name' => 'Consumer', 'email' => 'jbconsumer@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $winnerUser = User::create([
            'name' => 'Winner', 'email' => 'jbwinner@example.com', 'phone' => '+923002223333',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $winnerProfile = ProviderProfile::create([
            'user_id' => $winnerUser->id, 'business_name' => 'Winner Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $winnerProfile->id, 'service_id' => $service->id, 'price' => 3000, 'is_active' => true]);

        $loserUser = User::create([
            'name' => 'Loser', 'email' => 'jbloser@example.com', 'phone' => '+923003334444',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $loserProfile = ProviderProfile::create([
            'user_id' => $loserUser->id, 'business_name' => 'Loser Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        ProviderService::create(['provider_profile_id' => $loserProfile->id, 'service_id' => $service->id, 'price' => 3000, 'is_active' => true]);

        $job = JobPost::create([
            'reference' => 'JOB-TESTACC1', 'consumer_id' => $consumer->id, 'service_id' => $service->id,
            'description' => 'Fix wiring', 'address' => '1 Main St', 'city' => 'Karachi', 'status' => JobPost::STATUS_OPEN,
        ]);

        $this->actingAs($winnerUser)->post("/provider/jobs/{$job->id}/bids", [
            'amount' => 3000, 'proposed_date' => now()->addDay()->toDateString(), 'proposed_time' => '10:00',
        ])->assertRedirect();

        $this->actingAs($loserUser)->post("/provider/jobs/{$job->id}/bids", [
            'amount' => 3200, 'proposed_date' => now()->addDay()->toDateString(), 'proposed_time' => '11:00',
        ])->assertRedirect();

        $winningBid = Bid::where('provider_profile_id', $winnerProfile->id)->firstOrFail();

        $this->actingAs($consumer)->post("/jobs/{$job->id}/bids/{$winningBid->id}/accept")->assertRedirect();

        $job->refresh();
        $this->assertTrue($job->isAwarded());

        // The WINNING provider must have jobTaken=false (banner stays hidden via x-show),
        // and see their own "View booking" panel instead.
        $winnerResponse = $this->actingAs($winnerUser)->get("/provider/jobs/{$job->id}");
        $winnerResponse->assertOk();
        $winnerResponse->assertSee('jobTaken: false', false);
        $winnerResponse->assertSee('View booking');

        // The LOSING provider should still see the job is no longer open.
        $loserResponse = $this->actingAs($loserUser)->get("/provider/jobs/{$job->id}");
        $loserResponse->assertOk();
        $loserResponse->assertSee('jobTaken: true', false);
    }
}
