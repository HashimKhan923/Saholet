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
                'description' => 'Payment pending for booking ' . $payment->booking->reference,
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
                'description' => 'Pending cleared for booking ' . $payment->booking->reference,
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

    /**
     * Customer paid the provider in cash directly — Sahoulat never held this
     * money, so there's nothing to "release." Instead the commission becomes
     * a debt: a negative entry straight in the AVAILABLE bucket, tagged so
     * the auto-suspend job can find how long it's gone unpaid.
     */
    public function chargeCashCommission(Payment $payment, User $providerUser): void
    {
        $payment->loadMissing('booking');

        $rate = $this->commission->rateFor($payment->booking);
        $split = $this->commission->compute((float) $payment->amount, $rate);

        DB::transaction(function () use ($payment, $providerUser, $split) {
            $wallet = $this->walletFor($providerUser);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'bucket' => LedgerEntry::BUCKET_AVAILABLE,
                'type' => 'cash_commission_due',
                'amount' => -1 * $split['commission'],
                'description' => 'Commission owed for cash booking ' . $payment->booking->reference,
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

    /**
     * Admin has confirmed the customer's bank-transfer-to-Sahoulat screenshot
     * against the real account statement. The job is already complete by
     * this point (payment only happens post-completion), so there's no real
     * holding period — hold and release happen back-to-back in one request,
     * reusing the exact same escrow/commission logic as a card payment so
     * the ledger trail stays consistent across payment methods.
     */
    public function verifyAndReleaseBankTransfer(Payment $payment, User $providerUser, User $admin): void
    {
        $payment->update([
            'status' => Payment::STATUS_ESCROW,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        $this->holdInEscrow($payment, $providerUser);
        $this->release($payment, $providerUser);
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
                'description' => 'Pending payment refunded for booking ' . $payment->booking->reference,
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