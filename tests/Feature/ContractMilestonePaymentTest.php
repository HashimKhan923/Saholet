<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractMilestonePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_submit_get_quoted_accept_and_pay_a_contract_milestone(): void
    {
        $category = Category::create(['name' => 'Construction', 'slug' => 'construction', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Wiring', 'slug' => 'wiring',
            'base_price' => 5000, 'duration_minutes' => 120, 'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin.test@example.com', 'phone' => '+923003333333',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $consumer = User::create([
            'name' => 'Contract Consumer', 'email' => 'contract.consumer@example.com', 'phone' => '+923004444444',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $this->actingAs($consumer)->post('/contracts', [
            'title' => 'New Office Wiring',
            'description' => 'Full office electrical wiring for a new branch.',
            'address' => '12 Business Street, Lahore',
            'city' => 'Lahore',
            'items' => [
                ['service_id' => $service->id, 'quantity' => 1, 'notes' => 'Whole floor'],
            ],
        ])->assertRedirect();

        $contract = Contract::firstOrFail();
        $this->assertTrue($contract->isSubmitted());
        $item = $contract->items()->firstOrFail();

        $this->actingAs($admin)->post("/admin/contracts/{$contract->id}/quote", [
            'admin_notes' => 'Standard pricing.',
            'items' => [$item->id => ['price' => 40000]],
            'milestones' => [
                ['title' => 'Deposit', 'amount' => 15000],
                ['title' => 'Final', 'amount' => 25000],
            ],
        ])->assertRedirect();

        $contract->refresh();
        $this->assertTrue($contract->isQuoted());
        $this->assertSame('40000.00', (string) $contract->quoted_total);

        $this->actingAs($consumer)->post("/contracts/{$contract->id}/accept")->assertRedirect();
        $contract->refresh();
        $this->assertTrue($contract->isAccepted());

        $deposit = $contract->milestones()->where('title', 'Deposit')->firstOrFail();
        $this->assertTrue($deposit->isPayable());

        $this->actingAs($consumer)
            ->post("/contracts/{$contract->id}/milestones/{$deposit->id}/pay", ['gateway' => 'mock'])
            ->assertRedirect("/contracts/{$contract->id}");

        $deposit->refresh();
        $contract->refresh();
        $this->assertTrue($deposit->isEscrow());
        $this->assertSame(Contract::STATUS_IN_PROGRESS, $contract->status);

        $payment = Payment::where('contract_milestone_id', $deposit->id)->firstOrFail();
        $this->assertTrue($payment->isEscrow());
        $this->assertSame('15000.00', (string) $payment->amount);

        // A second milestone on the same (now in_progress) contract must still be payable.
        $final = $contract->milestones()->where('title', 'Final')->firstOrFail();
        $this->assertTrue($final->isPayable());

        // The deposit itself must not be payable twice.
        $this->assertFalse($deposit->fresh()->isPayable());
    }

    public function test_milestone_cannot_be_paid_before_the_quote_is_accepted(): void
    {
        $category = Category::create(['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id, 'name' => 'Pipe Fitting', 'slug' => 'pipe-fitting',
            'base_price' => 3000, 'duration_minutes' => 90, 'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin Two', 'email' => 'admin.test2@example.com', 'phone' => '+923005555555',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $consumer = User::create([
            'name' => 'Consumer Two', 'email' => 'consumer.test2@example.com', 'phone' => '+923006666666',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);

        $this->actingAs($consumer)->post('/contracts', [
            'title' => 'Bathroom Repipe',
            'description' => 'Repipe two bathrooms.',
            'address' => '5 Garden Road, Karachi',
            'city' => 'Karachi',
            'items' => [['service_id' => $service->id, 'quantity' => 2, 'notes' => '']],
        ]);

        $contract = Contract::firstOrFail();
        $item = $contract->items()->firstOrFail();

        $this->actingAs($admin)->post("/admin/contracts/{$contract->id}/quote", [
            'items' => [$item->id => ['price' => 6000]],
            'milestones' => [['title' => 'Deposit', 'amount' => 6000]],
        ]);

        $milestone = ContractMilestone::firstOrFail();

        // Contract is 'quoted', not yet 'accepted' — paying now must be blocked.
        $this->actingAs($consumer)
            ->get("/contracts/{$contract->id}/milestones/{$milestone->id}/pay")
            ->assertNotFound();
    }
}
