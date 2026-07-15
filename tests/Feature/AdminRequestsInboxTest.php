<?php

namespace Tests\Feature;

use App\Models\CareerApplication;
use App\Models\CareerListing;
use App\Models\Category;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRequestsInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_pending_items_across_all_four_categories(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        // Pending provider approval
        $providerUser = User::create([
            'name' => 'New Provider', 'email' => 'newprovider@example.com', 'phone' => '+923002223333',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        ProviderProfile::create([
            'user_id' => $providerUser->id, 'business_name' => 'Fresh Fix Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_PENDING, 'submitted_at' => now(),
        ]);

        // Submitted contract
        $category = Category::create(['name' => 'Construction', 'slug' => 'construction', 'is_active' => true]);
        Service::create([
            'category_id' => $category->id, 'name' => 'Wiring', 'slug' => 'wiring',
            'base_price' => 5000, 'duration_minutes' => 120, 'is_active' => true,
        ]);
        $consumer = User::create([
            'name' => 'Contract Consumer', 'email' => 'contractconsumer@example.com', 'phone' => '+923003334444',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
        Contract::create([
            'reference' => 'CTR-TEST01', 'consumer_id' => $consumer->id, 'title' => 'Office Rewiring',
            'description' => 'Rewire the office.', 'address' => 'Street 1, Lahore', 'city' => 'Lahore',
            'status' => Contract::STATUS_SUBMITTED,
        ]);

        // Open dispute (needs a booking + wallet chain would be heavy — Dispute model only needs booking_id FK)
        $providerUser2 = User::create([
            'name' => 'Dispute Provider', 'email' => 'disputeprovider@example.com', 'phone' => '+923004445555',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile2 = ProviderProfile::create([
            'user_id' => $providerUser2->id, 'business_name' => 'Dispute Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);
        $service2 = Service::create([
            'category_id' => $category->id, 'name' => 'AC Repair', 'slug' => 'ac-repair',
            'base_price' => 2000, 'duration_minutes' => 60, 'is_active' => true,
        ]);
        $booking = \App\Models\Booking::create([
            'reference' => 'BK-TEST01', 'consumer_id' => $consumer->id, 'provider_profile_id' => $profile2->id,
            'service_id' => $service2->id, 'address' => 'Street 2, Karachi', 'scheduled_date' => now()->addDay(),
            'scheduled_time' => '10:00', 'duration_minutes' => 60, 'price' => 2000, 'status' => \App\Models\Booking::STATUS_COMPLETED,
        ]);
        Dispute::create([
            'reference' => 'DSP-TEST01', 'booking_id' => $booking->id, 'opened_by' => $consumer->id,
            'opened_by_role' => 'consumer', 'reason' => 'Work not completed as agreed.',
            'status' => Dispute::STATUS_OPEN,
        ]);

        // New career application
        $jobSeeker = User::create([
            'name' => 'Job Seeker', 'email' => 'jobseeker@example.com', 'phone' => '+923005556666',
            'role' => User::ROLE_JOB_SEEKER, 'password' => 'password',
        ]);
        $careerCategory = \App\Models\CareerCategory::create(['name' => 'Trades', 'slug' => 'trades', 'is_active' => true]);
        $listing = CareerListing::create([
            'career_category_id' => $careerCategory->id, 'posted_by' => $admin->id, 'title' => 'Electrician',
            'slug' => 'electrician', 'description' => 'Join our team.', 'employment_type' => 'full_time',
            'city' => 'Karachi', 'status' => CareerListing::STATUS_OPEN,
        ]);
        CareerApplication::create([
            'career_listing_id' => $listing->id, 'user_id' => $jobSeeker->id,
            'resume_path' => 'resumes/js.pdf', 'resume_original_name' => 'js.pdf',
            'status' => CareerApplication::STATUS_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)->get('/admin/requests');

        $response->assertOk();
        $response->assertSee('Fresh Fix Co');
        $response->assertSee('Office Rewiring');
        $response->assertSee('DSP-TEST01');
        $response->assertSee('Electrician');
        $response->assertSee('4 items waiting on you');
    }
}
