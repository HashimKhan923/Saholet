<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\Notifier;
use App\Services\PaymentFinalizer;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bank-transfer-to-Sahoulat payments waiting on a human to cross-check the
 * screenshot against the real account statement before being confirmed.
 * Covers two sources: a booking's completion payment (work is already done —
 * verifying immediately releases the provider's wallet), and a contract
 * milestone payment (may be paid before any provider is even assigned to the
 * contract — verifying just moves it to escrow, same as a real gateway
 * success; the wallet only gets touched later, when a provider is assigned).
 */
class PaymentVerificationController extends Controller
{
    public function __construct(
        private WalletService $wallets,
        private InvoiceService $invoices,
        private PaymentFinalizer $finalizer,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $validStatuses = ['all', 'pending', 'verified', 'rejected'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $query = Payment::where('gateway', Payment::GATEWAY_BANK_TRANSFER)
            ->with(['booking.service', 'contractMilestone.contract', 'order', 'consumer'])
            ->latest();

        match ($status) {
            'pending' => $query->where('status', Payment::STATUS_PENDING),
            'verified' => $query->whereIn('status', [Payment::STATUS_ESCROW, Payment::STATUS_RELEASED]),
            'rejected' => $query->where('status', Payment::STATUS_FAILED),
            default => null,
        };

        $payments = $query->paginate(15)->withQueryString();

        $counts = [
            'pending' => Payment::where('gateway', Payment::GATEWAY_BANK_TRANSFER)->where('status', Payment::STATUS_PENDING)->count(),
        ];

        return view('admin.payments.index', compact('payments', 'status', 'counts'));
    }

    public function show(Payment $payment): View
    {
        abort_unless($payment->isBankTransfer(), 404);

        $payment->load(['booking.service', 'booking.providerProfile.user', 'contractMilestone.contract.consumer', 'order.providerProfile.user', 'order.items', 'consumer', 'verifier']);

        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->isBankTransfer(), 404);

        if (! $payment->isPending()) {
            return back()->with('error', 'This payment has already been processed.');
        }

        if ($payment->contract_milestone_id) {
            return $this->verifyMilestonePayment($payment, $request->user());
        }

        if ($payment->order_id) {
            return $this->verifyOrderPayment($payment, $request->user());
        }

        $payment->load('booking.providerProfile.user');
        $booking = $payment->booking;

        if (! $booking->isCompleted()) {
            // Prepaid before the job was done — hold it in escrow like any other
            // gateway charge instead of releasing to a job that isn't finished.
            $this->wallets->verifyBankTransferToEscrow($payment, $booking->providerProfile->user, $request->user());

            app(Notifier::class)->notify(
                $booking->providerProfile->user,
                'payment',
                'Payment verified',
                'The bank transfer for booking ' . $booking->reference . ' has been verified and is held pending until the job is complete.',
                route('provider.wallet.index')
            );

            app(Notifier::class)->notify(
                $payment->consumer,
                'payment',
                'Payment confirmed',
                'Your bank transfer for booking ' . $booking->reference . ' has been confirmed and is held safely until the job is complete.',
                route('consumer.bookings.show', $booking)
            );

            return back()->with('success', 'Payment verified and held pending until the job is complete.');
        }

        $this->wallets->verifyAndReleaseBankTransfer($payment, $booking->providerProfile->user, $request->user());
        $this->invoices->emailPaymentConfirmation($booking, $payment);

        app(Notifier::class)->notify(
            $booking->providerProfile->user,
            'payment',
            'Payment verified',
            'The bank transfer for booking ' . $booking->reference . ' has been verified and released to your wallet.',
            route('provider.wallet.index')
        );

        app(Notifier::class)->notify(
            $payment->consumer,
            'payment',
            'Payment confirmed',
            'Your bank transfer for booking ' . $booking->reference . ' has been confirmed. Thank you!',
            route('consumer.bookings.show', $booking)
        );

        return back()->with('success', 'Payment verified and released to the provider.');
    }

    private function verifyMilestonePayment(Payment $payment, User $admin): RedirectResponse
    {
        $payment->load('contractMilestone.contract.consumer');
        $milestone = $payment->contractMilestone;
        $contract = $milestone->contract;

        $payment->update(['verified_at' => now(), 'verified_by' => $admin->id]);
        $this->finalizer->finalizeMilestonePayment($payment, 'BANKTRANSFER-' . $payment->reference);

        app(Notifier::class)->notify(
            $contract->consumer,
            'payment',
            'Payment confirmed',
            'Your bank transfer for ' . $milestone->title . ' (' . $contract->reference . ') has been confirmed. Thank you!',
            route('consumer.contracts.show', $contract)
        );

        return back()->with('success', 'Payment verified and held in escrow for this contract.');
    }

    /**
     * An order's bank transfer is normally paid before fulfillment (see
     * CheckoutService), so verifying it here usually just moves it into
     * escrow — releasing happens later, when the provider marks the order
     * complete (see OrderFulfillmentService::complete()). But that release
     * is only triggered *at the moment* an order transitions to completed;
     * if the order is fast-fulfilled (e.g. self-pickup) before an admin
     * gets around to checking the bank statement, that trigger fires while
     * the payment is still `pending` and finds nothing to release — nothing
     * else ever re-checks it afterwards. So mirror the booking flow below:
     * if the order is already completed by the time this runs, release
     * straight to the provider's wallet instead of parking it in escrow.
     */
    private function verifyOrderPayment(Payment $payment, User $admin): RedirectResponse
    {
        $payment->load('order.providerProfile.user', 'order.consumer');
        $order = $payment->order;

        if ($order->isCompleted()) {
            $this->wallets->verifyAndReleaseBankTransfer($payment, $order->providerProfile->user, $admin);

            app(Notifier::class)->notify(
                $order->providerProfile->user,
                'payment',
                'Payment verified',
                'The bank transfer for order ' . $order->reference . ' has been verified and released to your wallet.',
                route('provider.orders.show', $order)
            );

            app(Notifier::class)->notify(
                $order->consumer,
                'payment',
                'Payment confirmed',
                'Your bank transfer for order ' . $order->reference . ' has been confirmed. Thank you!',
                route('consumer.orders.show', $order)
            );

            return back()->with('success', 'Payment verified and released to the provider.');
        }

        $this->wallets->verifyBankTransferToEscrow($payment, $order->providerProfile->user, $admin);

        app(Notifier::class)->notify(
            $order->providerProfile->user,
            'payment',
            'Payment verified',
            'The bank transfer for order ' . $order->reference . ' has been verified and is held pending until fulfilled.',
            route('provider.orders.show', $order)
        );

        app(Notifier::class)->notify(
            $order->consumer,
            'payment',
            'Payment confirmed',
            'Your bank transfer for order ' . $order->reference . ' has been confirmed.',
            route('consumer.orders.show', $order)
        );

        return back()->with('success', 'Payment verified and held pending until the order is fulfilled.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->isBankTransfer(), 404);

        if (! $payment->isPending()) {
            return back()->with('error', 'This payment has already been processed.');
        }

        $data = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $payment->update(['status' => Payment::STATUS_FAILED, 'notes' => $data['notes']]);

        if ($payment->contract_milestone_id) {
            $payment->load('contractMilestone.contract.consumer');
            $contract = $payment->contractMilestone->contract;

            app(Notifier::class)->notify(
                $contract->consumer,
                'payment',
                'Payment could not be verified',
                $data['notes'] . ' Please submit a new payment for ' . $payment->contractMilestone->title . '.',
                route('consumer.contracts.milestones.pay', [$contract, $payment->contractMilestone])
            );

            return back()->with('success', 'Payment rejected. The customer has been notified to resubmit.');
        }

        if ($payment->order_id) {
            $payment->load('order.consumer');
            $order = $payment->order;

            app(Notifier::class)->notify(
                $order->consumer,
                'payment',
                'Payment could not be verified',
                $data['notes'] . ' Please contact support about order ' . $order->reference . '.',
                route('consumer.orders.show', $order)
            );

            return back()->with('success', 'Payment rejected. The customer has been notified.');
        }

        $payment->load('booking.consumer');

        app(Notifier::class)->notify(
            $payment->booking->consumer,
            'payment',
            'Payment could not be verified',
            $data['notes'] . ' Please submit a new payment for booking ' . $payment->booking->reference . '.',
            route('consumer.payments.complete.create', $payment->booking)
        );

        return back()->with('success', 'Payment rejected. The customer has been notified to resubmit.');
    }
}
