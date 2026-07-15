<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Turns a successful gateway charge into an escrowed payment. Shared by the
 * synchronous mock-gateway checkout (Consumer\PaymentController,
 * Consumer\ContractController) and the async redirect-gateway return handler
 * (PaymentReturnController) so both paths move money through the exact same
 * code — one escrow implementation, not two that can drift apart.
 */
class PaymentFinalizer
{
    public function __construct(private WalletService $wallets, private ReferralService $referrals) {}

    public function finalizeBookingPayment(Payment $payment, string $gatewayReference): void
    {
        $payment->loadMissing('booking.providerProfile.user');

        DB::transaction(function () use ($payment, $gatewayReference) {
            $payment->update([
                'status' => Payment::STATUS_ESCROW,
                'gateway_reference' => $gatewayReference,
                'paid_at' => now(),
            ]);

            $this->wallets->holdInEscrow($payment, $payment->booking->providerProfile->user);
            $this->redeemCredit($payment);
        });

        $this->referrals->rewardFirstPayment($payment);
    }

    public function finalizeMilestonePayment(Payment $payment, string $gatewayReference): void
    {
        $payment->loadMissing('contractMilestone.contract');
        $milestone = $payment->contractMilestone;

        DB::transaction(function () use ($payment, $milestone, $gatewayReference) {
            $payment->update([
                'status' => Payment::STATUS_ESCROW,
                'gateway_reference' => $gatewayReference,
                'paid_at' => now(),
            ]);

            $milestone->update([
                'status' => ContractMilestone::STATUS_ESCROW,
                'gateway' => $payment->gateway,
                'gateway_reference' => $gatewayReference,
                'paid_at' => now(),
            ]);

            if ($milestone->contract->isAccepted()) {
                $milestone->contract->update(['status' => Contract::STATUS_IN_PROGRESS]);
            }

            $this->redeemCredit($payment);
        });

        $this->referrals->rewardFirstPayment($payment);
    }

    /**
     * Debits the consumer's referral credit balance for the amount already
     * baked into this payment's escrow — done only once, at the moment the
     * payment actually clears, so an abandoned/failed attempt never touches
     * the balance.
     */
    private function redeemCredit(Payment $payment): void
    {
        if ((float) $payment->credit_applied <= 0) {
            return;
        }

        $payment->loadMissing('consumer');
        $payment->consumer?->decrement('credit_balance', (float) $payment->credit_applied);
    }
}
