<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\PaymentManager;
use App\Services\PaymentFinalizer;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentManager $payments,
        private WalletService $wallets,
        private PaymentFinalizer $finalizer,
    ) {}

    /** Available gateways + how much referral credit can be applied, before paying. */
    public function options(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user', 'payments']);

        abort_unless($booking->isPayable(), 404);

        $gateways = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false) || $g->key() === 'mock')
            ->map(fn ($g) => ['key' => $g->key(), 'label' => $g->label()])
            ->values();

        return response()->json([
            'gateways' => $gateways,
            'max_credit_applicable' => min((float) $request->user()->credit_balance, (float) $booking->price),
            'amount' => (float) $booking->price,
        ]);
    }

    /** Pay a pending booking into escrow. Body: gateway (required unless credit fully covers it), apply_credit (bool). */
    public function store(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user', 'payments']);

        if (! $booking->isPayable()) {
            return response()->json(['message' => 'This booking is not payable.'], 422);
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

            return response()->json([
                'message' => 'Paid entirely with your referral credit — held safely in escrow until the job is complete.',
                'payment' => new PaymentResource($payment->fresh()),
            ], 201);
        }

        $gateway = $this->payments->driver($data['gateway']);

        if (! $gateway->isAvailable()) {
            return response()->json(['message' => $gateway->label() . ' is not available. Please choose another method.'], 422);
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

            return response()->json(['message' => $result->message ?? 'Payment failed.'], 422);
        }

        if ($result->status === 'pending') {
            return response()->json([
                'status' => 'pending',
                'redirect_url' => $result->redirectUrl,
                'redirect_fields' => $result->redirectFields,
            ]);
        }

        $this->finalizer->finalizeBookingPayment($payment, $result->gatewayReference);

        return response()->json([
            'message' => 'Payment received and held safely in escrow until the job is complete.',
            'payment' => new PaymentResource($payment->fresh()),
        ], 201);
    }

    /** Release escrow to the provider once the job is completed and undisputed. */
    public function release(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('release', $booking);

        $booking->load(['providerProfile.user', 'payments', 'dispute']);

        if (! $booking->isCompleted()) {
            return response()->json(['message' => 'You can release payment once the provider marks the job complete.'], 422);
        }

        if ($booking->hasOpenDispute()) {
            return response()->json(['message' => 'This booking has an open dispute. Payment can\'t be released until it is resolved.'], 422);
        }

        $payment = $booking->activePayment();

        if (! $payment || ! $payment->isEscrow()) {
            return response()->json(['message' => 'There is no escrow payment to release for this booking.'], 422);
        }

        $this->wallets->release($payment, $booking->providerProfile->user);

        app(\App\Services\Notifier::class)->notify(
            $booking->providerProfile->user,
            'payment',
            'Payment released',
            'Payment for booking ' . $booking->reference . ' has been released to your wallet.',
            route('provider.wallet.index')
        );

        return response()->json([
            'message' => 'Payment released to the provider. Thank you!',
            'booking' => new BookingResource($booking->fresh(['service', 'providerProfile.user', 'payments'])),
        ]);
    }

    private function generateReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}
