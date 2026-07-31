<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\Notifier;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bank-transfer-to-Sahoulat payments (submitted after a booking completes)
 * waiting on a human to cross-check the screenshot against the real account
 * statement before the amount is released to the provider's wallet.
 */
class PaymentVerificationController extends Controller
{
    public function __construct(private WalletService $wallets, private InvoiceService $invoices) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $validStatuses = ['all', 'pending', 'verified', 'rejected'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $query = Payment::where('gateway', Payment::GATEWAY_BANK_TRANSFER)
            ->with(['booking.service', 'consumer'])
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

        $payment->load(['booking.service', 'booking.providerProfile.user', 'consumer', 'verifier']);

        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->isBankTransfer(), 404);

        if (! $payment->isPending()) {
            return back()->with('error', 'This payment has already been processed.');
        }

        $payment->load('booking.providerProfile.user');

        $this->wallets->verifyAndReleaseBankTransfer($payment, $payment->booking->providerProfile->user, $request->user());
        $this->invoices->emailPaymentConfirmation($payment->booking, $payment);

        app(Notifier::class)->notify(
            $payment->booking->providerProfile->user,
            'payment',
            'Payment verified',
            'The bank transfer for booking ' . $payment->booking->reference . ' has been verified and released to your wallet.',
            route('provider.wallet.index')
        );

        app(Notifier::class)->notify(
            $payment->consumer,
            'payment',
            'Payment confirmed',
            'Your bank transfer for booking ' . $payment->booking->reference . ' has been confirmed. Thank you!',
            route('consumer.bookings.show', $payment->booking)
        );

        return back()->with('success', 'Payment verified and released to the provider.');
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

        $payment->load('booking.consumer');
        $payment->update(['status' => Payment::STATUS_FAILED, 'notes' => $data['notes']]);

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
