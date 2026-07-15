<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Payouts here are manual: a request debits the provider's available balance
 * immediately (so the same funds can't be requested twice), an admin sends
 * the money via real bank transfer/JazzCash outside Sahoulet, then marks the
 * request paid. Rejecting a request reverses the ledger hold. No automated
 * disbursement API is wired up — that requires a payout-specific merchant
 * agreement Sahoulet doesn't have, distinct from the checkout gateways.
 */
class WithdrawalService
{
    public function __construct(private WalletService $wallets) {}

    public function request(ProviderProfile $profile, float $amount): WithdrawalRequest
    {
        $wallet = $this->wallets->walletFor($profile->user);

        return DB::transaction(function () use ($profile, $wallet, $amount) {
            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'bucket' => LedgerEntry::BUCKET_AVAILABLE,
                'type' => 'withdrawal_hold',
                'amount' => -1 * $amount,
                'description' => 'Withdrawal requested',
            ]);

            $this->wallets->recompute($wallet);

            return WithdrawalRequest::create([
                'reference' => $this->generateReference(),
                'provider_profile_id' => $profile->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'status' => WithdrawalRequest::STATUS_PENDING,
                'payout_method' => $profile->payout_method,
                'payout_account_title' => $profile->payout_account_title,
                'payout_account_number' => $profile->payout_account_number,
                'payout_bank_name' => $profile->payout_bank_name,
            ]);
        });
    }

    public function markPaid(WithdrawalRequest $request, User $admin): void
    {
        $request->update([
            'status' => WithdrawalRequest::STATUS_PAID,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);
    }

    public function reject(WithdrawalRequest $request, User $admin, ?string $reason): void
    {
        DB::transaction(function () use ($request, $admin, $reason) {
            LedgerEntry::create([
                'wallet_id' => $request->wallet_id,
                'bucket' => LedgerEntry::BUCKET_AVAILABLE,
                'type' => 'withdrawal_reversed',
                'amount' => $request->amount,
                'description' => 'Withdrawal request rejected — funds returned to available balance',
            ]);

            $this->wallets->recompute($request->wallet);

            $request->update([
                'status' => WithdrawalRequest::STATUS_REJECTED,
                'admin_notes' => $reason,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);
        });
    }

    private function generateReference(): string
    {
        do {
            $ref = 'WD-' . strtoupper(Str::random(8));
        } while (WithdrawalRequest::where('reference', $ref)->exists());

        return $ref;
    }
}
