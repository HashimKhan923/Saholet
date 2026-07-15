<?php

namespace Tests\Feature;

use App\Models\LedgerEntry;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private function makeProviderWithBalance(float $available = 5000): array
    {
        $user = User::create([
            'name' => 'Provider', 'email' => 'wdprovider@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_PROVIDER, 'password' => 'password',
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id, 'business_name' => 'Wd Co', 'city' => 'Karachi',
            'status' => ProviderProfile::STATUS_APPROVED,
        ]);

        $wallet = app(WalletService::class)->walletFor($user);
        LedgerEntry::create([
            'wallet_id' => $wallet->id, 'bucket' => LedgerEntry::BUCKET_AVAILABLE,
            'type' => 'release_in', 'amount' => $available, 'description' => 'Test earnings',
        ]);
        app(WalletService::class)->recompute($wallet);

        return [$user, $profile, $wallet->fresh()];
    }

    public function test_provider_must_set_up_payout_method_before_withdrawing(): void
    {
        [$user] = $this->makeProviderWithBalance();

        $this->actingAs($user)->post('/provider/withdrawals', ['amount' => 1000])
            ->assertRedirect();

        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_provider_can_request_a_withdrawal_and_available_balance_is_held(): void
    {
        [$user, $profile, $wallet] = $this->makeProviderWithBalance(5000);

        $this->actingAs($user)->post('/provider/payout-method', [
            'payout_method' => 'jazzcash',
            'payout_account_title' => 'Test Provider',
            'payout_account_number' => '03001234567',
        ])->assertRedirect();

        $this->actingAs($user)->post('/provider/withdrawals', ['amount' => 2000])
            ->assertRedirect();

        $withdrawal = WithdrawalRequest::firstOrFail();
        $this->assertTrue($withdrawal->isPending());
        $this->assertSame('2000.00', (string) $withdrawal->amount);
        $this->assertSame('jazzcash', $withdrawal->payout_method);
        $this->assertSame('03001234567', $withdrawal->payout_account_number);

        // Available balance immediately drops by the requested amount.
        $this->assertSame('3000.00', (string) $wallet->fresh()->available_balance);
    }

    public function test_provider_cannot_request_more_than_available_balance(): void
    {
        [$user, $profile] = $this->makeProviderWithBalance(1000);

        $this->actingAs($user)->post('/provider/payout-method', [
            'payout_method' => 'bank',
            'payout_account_title' => 'Test Provider',
            'payout_account_number' => '1234567890',
            'payout_bank_name' => 'HBL',
        ])->assertRedirect();

        $this->actingAs($user)->post('/provider/withdrawals', ['amount' => 5000])
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_admin_can_mark_a_withdrawal_paid(): void
    {
        [$user, $profile, $wallet] = $this->makeProviderWithBalance(5000);
        $admin = User::create([
            'name' => 'Admin', 'email' => 'wdadmin@example.com', 'phone' => '+923002223333',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        $this->actingAs($user)->post('/provider/payout-method', [
            'payout_method' => 'easypaisa',
            'payout_account_title' => 'Test Provider',
            'payout_account_number' => '03009876543',
        ])->assertRedirect();
        $this->actingAs($user)->post('/provider/withdrawals', ['amount' => 1500])->assertRedirect();

        $withdrawal = WithdrawalRequest::firstOrFail();

        $this->actingAs($admin)->post("/admin/withdrawals/{$withdrawal->id}/paid")->assertRedirect();

        $withdrawal->refresh();
        $this->assertSame('paid', $withdrawal->status);
        $this->assertSame($admin->id, $withdrawal->processed_by);
        $this->assertNotNull($withdrawal->processed_at);

        // Balance stays debited — the money genuinely left.
        $this->assertSame('3500.00', (string) $wallet->fresh()->available_balance);
    }

    public function test_admin_reject_returns_funds_to_available_balance(): void
    {
        [$user, $profile, $wallet] = $this->makeProviderWithBalance(5000);
        $admin = User::create([
            'name' => 'Admin', 'email' => 'wdadmin2@example.com', 'phone' => '+923003334444',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        $this->actingAs($user)->post('/provider/payout-method', [
            'payout_method' => 'bank',
            'payout_account_title' => 'Test Provider',
            'payout_account_number' => '1234567890',
            'payout_bank_name' => 'HBL',
        ])->assertRedirect();
        $this->actingAs($user)->post('/provider/withdrawals', ['amount' => 2000])->assertRedirect();

        $withdrawal = WithdrawalRequest::firstOrFail();
        $this->assertSame('3000.00', (string) $wallet->fresh()->available_balance);

        $this->actingAs($admin)
            ->post("/admin/withdrawals/{$withdrawal->id}/reject", ['admin_notes' => 'Bad account number'])
            ->assertRedirect();

        $withdrawal->refresh();
        $this->assertSame('rejected', $withdrawal->status);
        $this->assertSame('Bad account number', $withdrawal->admin_notes);

        // Full balance restored.
        $this->assertSame('5000.00', (string) $wallet->fresh()->available_balance);
    }
}
