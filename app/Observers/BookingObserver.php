<?php

namespace App\Observers;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\ContractItem;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\Notifier;
use App\Services\WalletService;

class BookingObserver
{
    public function __construct(private WalletService $wallets, private InvoiceService $invoices) {}

    public function updated(Booking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        // A contract-derived booking finishing its own work doesn't automatically
        // mean the contract is done (other items/milestones may still be open) —
        // it just marks its own item so the contract's completion gate can see it.
        if ($booking->status === Booking::STATUS_COMPLETED && $booking->contract_item_id) {
            $booking->contractItem?->update(['status' => ContractItem::STATUS_COMPLETED]);
        }

        if ($booking->status === Booking::STATUS_COMPLETED) {
            $this->settlePrepaidCash($booking);
        }

        try {
            broadcast(new BookingStatusUpdated($booking));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * A customer can commit to "cash" before the job is done (see
     * Consumer\PaymentController::storeCashOrBankTransfer()) — nothing is
     * actually charged at that point since cash only changes hands once the
     * provider is there. Now that the job is genuinely complete, settle it
     * exactly like Consumer\CompletionPaymentController does.
     */
    private function settlePrepaidCash(Booking $booking): void
    {
        $payment = $booking->payments()
            ->where('gateway', Payment::GATEWAY_CASH)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if (! $payment) {
            return;
        }

        $booking->loadMissing('providerProfile.user');

        $this->wallets->chargeCashCommission($payment, $booking->providerProfile->user);
        $this->invoices->emailPaymentConfirmation($booking, $payment);

        app(Notifier::class)->notify(
            $booking->providerProfile->user,
            'payment',
            'Cash payment settled',
            'Booking ' . $booking->reference . ' is complete. Commission for the cash payment has been deducted from your wallet.',
            route('provider.wallet.index')
        );
    }
}
