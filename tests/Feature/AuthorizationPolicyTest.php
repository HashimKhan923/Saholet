<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\CorporateAccount;
use App\Models\EmergencyRequest;
use App\Models\JobPost;
use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Spot-checks the Policy-based authorization refactor (replacing scattered
 * abort_unless ownership checks): for a representative sample of protected
 * resources, a stranger is rejected (403) and the real owner still succeeds.
 */
class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function consumer(string $email): User
    {
        return User::create([
            'name' => 'User ' . $email, 'email' => $email, 'phone' => '+92300' . random_int(1000000, 9999999),
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
    }

    public function test_address_ownership_is_enforced_by_policy(): void
    {
        $owner = $this->consumer('addr-owner@example.com');
        $stranger = $this->consumer('addr-stranger@example.com');

        $address = Address::create([
            'user_id' => $owner->id, 'label' => 'Home', 'address' => '1 Main St', 'city' => 'Karachi',
        ]);

        $this->actingAs($stranger)->put("/addresses/{$address->id}", ['label' => 'Hacked', 'address' => 'x', 'city' => 'x'])
            ->assertForbidden();

        $this->actingAs($owner)->put("/addresses/{$address->id}", ['label' => 'Updated', 'address' => '1 Main St', 'city' => 'Karachi'])
            ->assertRedirect();

        $this->assertSame('Updated', $address->fresh()->label);
    }

    public function test_notification_ownership_is_enforced_by_policy(): void
    {
        $owner = $this->consumer('notif-owner@example.com');
        $stranger = $this->consumer('notif-stranger@example.com');

        $notification = Notification::create([
            'user_id' => $owner->id, 'type' => 'test', 'title' => 'Hi', 'body' => 'Body',
        ]);

        $this->actingAs($stranger)->post("/notifications/{$notification->id}/read")->assertForbidden();
        $this->actingAs($owner)->post("/notifications/{$notification->id}/read")->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_job_post_ownership_is_enforced_by_policy(): void
    {
        $owner = $this->consumer('job-owner@example.com');
        $stranger = $this->consumer('job-stranger@example.com');

        $category = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing-jp', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Pipe Work', 'slug' => 'pipe-work',
            'base_price' => 1000, 'duration_minutes' => 30, 'is_active' => true,
        ]);
        $jobPost = JobPost::create([
            'reference' => 'JOB-TEST01', 'consumer_id' => $owner->id, 'service_id' => $service->id,
            'description' => 'Fix pipes', 'city' => 'Karachi', 'address' => '1 Main St', 'status' => JobPost::STATUS_OPEN,
        ]);

        $this->actingAs($stranger)->get("/jobs/{$jobPost->id}")->assertForbidden();
        $this->actingAs($owner)->get("/jobs/{$jobPost->id}")->assertOk();
    }

    public function test_emergency_request_ownership_is_enforced_by_policy(): void
    {
        $owner = $this->consumer('em-owner@example.com');
        $stranger = $this->consumer('em-stranger@example.com');

        $category = Category::create(['name' => 'Electrical', 'slug' => 'electrical-em', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Wiring Fix', 'slug' => 'wiring-fix',
            'base_price' => 1500, 'duration_minutes' => 45, 'is_active' => true,
        ]);
        $emergency = EmergencyRequest::create([
            'reference' => 'EMG-TEST01', 'consumer_id' => $owner->id, 'service_id' => $service->id,
            'address' => '1 Main St', 'city' => 'Karachi', 'status' => EmergencyRequest::STATUS_OPEN,
        ]);

        $this->actingAs($stranger)->get("/emergencies/{$emergency->id}")->assertForbidden();
        $this->actingAs($owner)->get("/emergencies/{$emergency->id}")->assertOk();
    }

    public function test_corporate_account_create_gate_blocks_a_user_who_already_has_one(): void
    {
        $owner = $this->consumer('corp-owner@example.com');
        $account = CorporateAccount::create([
            'name' => 'Acme', 'owner_id' => $owner->id, 'billing_email' => 'billing@acme.com',
        ]);
        $owner->update(['corporate_account_id' => $account->id, 'corporate_role' => CorporateAccount::ROLE_OWNER]);

        $this->actingAs($owner)->get('/company')->assertForbidden();
    }
}
