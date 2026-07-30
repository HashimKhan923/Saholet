<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\ProviderProfile;
use App\Models\ProviderSettlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The reverse of a withdrawal — a provider paying down commission owed from
 * cash bookings. Submitting a settlement does NOT touch the wallet; only an
 * admin confirming it (with the amount actually received, which may be less
 * than claimed) posts the ledger entry that clears the negative balance.
 */
class ProviderSettlementService
{
    public function __construct(private WalletService $wallets) {}

    public function submit(ProviderProfile $profile, string $method, float $amount, ?string $screenshotPath): ProviderSettlement
    {
        $wallet = $this->wallets->walletFor($profile->user);

        return ProviderSettlement::create([
            'reference' => $this->generateReference(),
            'provider_profile_id' => $profile->id,
            'wallet_id' => $wallet->id,
            'method' => $method,
            'amount' => $amount,
            'screenshot_path' => $screenshotPath,
            'status' => ProviderSettlement::STATUS_PENDING,
        ]);
    }

    public function confirm(ProviderSettlement $settlement, float $confirmedAmount, User $admin, ?string $notes): void
    {
        DB::transaction(function () use ($settlement, $confirmedAmount, $admin, $notes) {
            $wallet = $settlement->wallet;

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'bucket' => LedgerEntry::BUCKET_AVAILABLE,
                'type' => 'commission_settled',
                'amount' => $confirmedAmount,
                'description' => 'Commission settlement ' . $settlement->reference . ' confirmed',
            ]);

            $this->wallets->recompute($wallet);

            $settlement->update([
                'status' => ProviderSettlement::STATUS_CONFIRMED,
                'confirmed_amount' => $confirmedAmount,
                'confirmed_by' => $admin->id,
                'confirmed_at' => now(),
                'admin_notes' => $notes,
            ]);

            $wallet->refresh();
            $profile = $settlement->providerProfile;

            if ($profile->isSuspended() && (float) $wallet->available_balance >= 0) {
                $profile->update(['suspended_at' => null, 'suspension_reason' => null]);
            }
        });
    }

    public function reject(ProviderSettlement $settlement, User $admin, ?string $notes): void
    {
        $settlement->update([
            'status' => ProviderSettlement::STATUS_REJECTED,
            'confirmed_by' => $admin->id,
            'confirmed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    private function generateReference(): string
    {
        do {
            $ref = 'STL-' . strtoupper(Str::random(8));
        } while (ProviderSettlement::where('reference', $ref)->exists());

        return $ref;
    }
}
