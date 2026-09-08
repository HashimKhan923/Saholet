<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The provider web pages for coupons, bids and open jobs were paginated
 * (15/page) to handle real volume — this confirms their /api/provider/*
 * mirrors got the same fix, since a mobile client hitting these directly
 * would otherwise silently receive an unbounded, ever-growing list.
 */
class ApiProviderPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function approvedProvider(): array
    {
        $profile = ProviderProfile::factory()->sellsProducts()->create();

        return [$profile->user, $profile];
    }

    public function test_provider_coupons_index_paginates(): void
    {
        [$user, $profile] = $this->approvedProvider();
        Sanctum::actingAs($user);

        Coupon::factory()->for($profile, 'providerProfile')->count(20)->create();

        $response = $this->getJson('/api/provider/coupons');

        $response->assertOk();
        $this->assertCount(15, $response->json('coupons'));
        $this->assertSame(20, $response->json('pagination.total'));
    }

    public function test_provider_bids_index_paginates(): void
    {
        [$user, $profile] = $this->approvedProvider();
        Sanctum::actingAs($user);

        $category = Category::create(['name' => 'Electrical', 'slug' => 'electrical-api-pg', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Wiring', 'slug' => 'wiring-api-pg',
            'base_price' => 3000, 'duration_minutes' => 60, 'is_active' => true,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 3000, 'is_active' => true]);
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);

        for ($i = 0; $i < 20; $i++) {
            $job = JobPost::create([
                'reference' => 'JOB-APIPG' . $i, 'consumer_id' => $consumer->id, 'service_id' => $service->id,
                'description' => 'Fix wiring', 'address' => '1 Main St', 'city' => 'Karachi', 'status' => JobPost::STATUS_OPEN,
            ]);
            Bid::create([
                'reference' => 'BID-APIPG' . $i, 'job_post_id' => $job->id, 'provider_profile_id' => $profile->id,
                'amount' => 3000, 'proposed_date' => now()->addDay()->toDateString(), 'proposed_time' => '10:00',
                'status' => Bid::STATUS_PENDING,
            ]);
        }

        $response = $this->getJson('/api/provider/bids');

        $response->assertOk();
        $this->assertCount(15, $response->json('bids'));
        $this->assertSame(20, $response->json('pagination.total'));
    }

    public function test_provider_open_jobs_index_paginates(): void
    {
        [$user, $profile] = $this->approvedProvider();
        Sanctum::actingAs($user);

        $category = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing-api-pg', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Pipe fix', 'slug' => 'pipe-fix-api-pg',
            'base_price' => 2000, 'duration_minutes' => 60, 'is_active' => true,
        ]);
        ProviderService::create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'price' => 2000, 'is_active' => true]);
        $consumer = User::factory()->create(['role' => User::ROLE_CONSUMER]);

        for ($i = 0; $i < 20; $i++) {
            JobPost::create([
                'reference' => 'JOB-APIPGJ' . $i, 'consumer_id' => $consumer->id, 'service_id' => $service->id,
                'description' => 'Fix pipe', 'address' => '1 Main St', 'city' => 'Karachi', 'status' => JobPost::STATUS_OPEN,
            ]);
        }

        $response = $this->getJson('/api/provider/jobs');

        $response->assertOk();
        $this->assertCount(15, $response->json('jobs'));
        $this->assertSame(20, $response->json('pagination.total'));
    }
}
