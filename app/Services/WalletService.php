<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(private CommissionService $commission) {}

    public function walletFor(User $user): Wallet
    {
        return Wallet::firstOrCreate(['user_id' => $user->id]);
    }

    /** Consumer paid → hold the full amount in the PROVIDER's escrow bucket. */
    public function holdInEscrow(Payment $payment, User $providerUser): void
    {
        DB::transaction(function () use ($payment, $providerUser) {
            $wallet = $this->walletFor($providerUser);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'bucket' => LedgerEntry::BUCKET_ESCROW,
                'type' => 'hold',
                'amount' => $payment->amount,
                'description' => 'Escrow hold for booking ' . $payment->booking->reference,
            ]);

            $this->recompute($wallet);
        });
    }

    /** Confirmed complete → remove escrow, credit provider the NET (minus commission). */
    public function release(Payment $payment, User $providerUser): void
    {
        $payment->loadMissing('booking');

        $rate = $this->commission->rateFor($payment->booking);
        $split = $this->commission->compute((float) $payment->amount, $rate);

        DB::transaction(function () use ($payment, $providerUser, $split) {
            $wallet = $this->walletFor($providerUser);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'bucket' => LedgerEntry::BUCKET_ESCROW,
                'type' => 'release_out',
                'amount' => -1 * $payment->amount,
                'description' => 'Escrow released for booking ' . $payment->booking->reference,
            ]);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'bucket' => LedgerEntry::BUCKET_AVAILABLE,
                'type' => 'release_in',
                'amount' => $split['provider'],
                'description' => 'Earnings for booking ' . $payment->booking->reference
                    . ' (after ' . rtrim(rtrim(number_format($split['rate'], 2), '0'), '.') . '% commission)',
            ]);

            $this->recompute($wallet);

            $payment->update([
                'status' => Payment::STATUS_RELEASED,
                'released_at' => now(),
                'commission_rate' => $split['rate'],
                'commission_amount' => $split['commission'],
                'provider_amount' => $split['provider'],
            ]);
        });
    }

    /** Booking cancelled/refunded after payment → remove escrow hold (full refund). */
    public function refund(Payment $payment, User $providerUser): void
    {
        DB::transaction(function () use ($payment, $providerUser) {
            $wallet = $this->walletFor($providerUser);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'bucket' => LedgerEntry::BUCKET_ESCROW,
                'type' => 'refund_out',
                'amount' => -1 * $payment->amount,
                'description' => 'Escrow refunded for booking ' . $payment->booking->reference,
            ]);

            $this->recompute($wallet);

            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'refunded_at' => now(),
            ]);
        });
    }

    /** Recompute cached balances from the append-only ledger. */
    public function recompute(Wallet $wallet): void
    {
        $escrow = (float) $wallet->entries()
            ->where('bucket', LedgerEntry::BUCKET_ESCROW)
            ->sum('amount');

        $available = (float) $wallet->entries()
            ->where('bucket', LedgerEntry::BUCKET_AVAILABLE)
            ->sum('amount');

        $wallet->update([
            'escrow_balance' => $escrow,
            'available_balance' => $available,
        ]);
    }
}