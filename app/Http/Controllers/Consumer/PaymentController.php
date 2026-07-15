<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\PaymentManager;
use App\Services\PaymentFinalizer;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentManager $payments,
        private WalletService $wallets,
        private PaymentFinalizer $finalizer,
    ) {}

    public function create(Request $request, Booking $booking): View
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user', 'payments']);

        abort_unless($booking->isPayable(), 404);

        $gateways = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false) || $g->key() === 'mock')
            ->values();

        $maxCreditApplicable = min((float) $request->user()->credit_balance, (float) $booking->price);

        return view('consumer.payments.create', compact('booking', 'gateways', 'maxCreditApplicable'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse|View
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user', 'payments']);

        if (! $booking->isPayable()) {
            return redirect()
                ->route('consumer.bookings.show', $booking)
                ->with('error', 'This booking is not payable.');
        }

        $consumer = $request->user();
        $creditApplied = $request->boolean('apply_credit')
            ? min((float) $consumer->credit_balance, (float) $booking->price)
            : 0.0;
        $fullyCoveredByCredit = $creditApplied >= (float) $booking->price;

        $available = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false) || $g->key() === 'mock')
            ->map->key()
            ->all();

        $data = $request->validate([
            'gateway' => [$fullyCoveredByCredit ? 'nullable' : 'required', Rule::in($available)],
        ]);

        if ($fullyCoveredByCredit) {
            $payment = Payment::create([
                'reference' => $this->generateReference(),
                'booking_id' => $booking->id,
                'consumer_id' => $consumer->id,
                'gateway' => 'credit',
                'amount' => $booking->price,
                'credit_applied' => $creditApplied,
                'status' => Payment::STATUS_PENDING,
            ]);

            $this->finalizer->finalizeBookingPayment($payment, 'CREDIT-' . $payment->reference);

            return redirect()
                ->route('consumer.bookings.show', $booking)
                ->with('success', 'Paid entirely with your referral credit — held safely in escrow until the job is complete.');
        }

        $gateway = $this->payments->driver($data['gateway']);

        if (! $gateway->isAvailable()) {
            return back()->with('error', $gateway->label() . ' is not available. Please choose another method.');
        }

        $payment = Payment::create([
            'reference' => $this->generateReference(),
            'booking_id' => $booking->id,
            'consumer_id' => $consumer->id,
            'gateway' => $gateway->key(),
            'amount' => $booking->price,
            'credit_applied' => $creditApplied,
            'status' => Payment::STATUS_PENDING,
        ]);

        $result = $gateway->charge($payment);

        if (! $result->success) {
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return back()->with('error', $result->message ?? 'Payment failed.');
        }

        if ($result->status === 'pending') {
            return view('payments.redirect', [
                'redirectUrl' => $result->redirectUrl,
                'redirectFields' => $result->redirectFields,
            ]);
        }

        $this->finalizer->finalizeBookingPayment($payment, $result->gatewayReference);

        return redirect()
            ->route('consumer.bookings.show', $booking)
            ->with('success', 'Payment received and held safely in escrow until the job is complete.');
    }

    public function release(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('release', $booking);

        $booking->load(['providerProfile.user', 'payments', 'dispute']);

        if (! $booking->isCompleted()) {
            return back()->with('error', 'You can release payment once the provider marks the job complete.');
        }

        if ($booking->hasOpenDispute()) {
            return back()->with('error', 'This booking has an open dispute. Payment can’t be released until it is resolved.');
        }

        $payment = $booking->activePayment();

        if (! $payment || ! $payment->isEscrow()) {
            return back()->with('error', 'There is no escrow payment to release for this booking.');
        }

        $this->wallets->release($payment, $booking->providerProfile->user);

        app(\App\Services\Notifier::class)->notify(
            $booking->providerProfile->user,
            'payment',
            'Payment released',
            'Payment for booking ' . $booking->reference . ' has been released to your wallet.',
            route('provider.wallet.index')
        );

        return back()->with('success', 'Payment released to the provider. Thank you!');
    }

    private function generateReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}