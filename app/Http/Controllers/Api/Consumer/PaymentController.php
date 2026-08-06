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
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false))
            ->map(fn ($g) => ['key' => $g->key(), 'label' => $g->label()])
            ->values()
            ->push(['key' => Payment::GATEWAY_CASH, 'label' => 'Cash'])
            ->push(['key' => Payment::GATEWAY_BANK_TRANSFER, 'label' => 'Bank transfer']);

        return response()->json([
            'gateways' => $gateways,
            'max_credit_applicable' => min((float) $request->user()->credit_balance, (float) $booking->price),
            'amount' => (float) $booking->price,
            'company_account' => config('payments.company_account'),
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
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false))
            ->map->key()
            ->push(Payment::GATEWAY_CASH)
            ->push(Payment::GATEWAY_BANK_TRANSFER)
            ->all();

        $data = $request->validate([
            'gateway' => [$fullyCoveredByCredit ? 'nullable' : 'required', Rule::in($available)],
            'screenshot' => [
                Rule::requiredIf(! $fullyCoveredByCredit && $request->input('gateway') === Payment::GATEWAY_BANK_TRANSFER),
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192',
            ],
        ]);

        if (! $fullyCoveredByCredit && in_array($data['gateway'], [Payment::GATEWAY_CASH, Payment::GATEWAY_BANK_TRANSFER], true)) {
            return $this->storeCashOrBankTransfer($request, $booking, $consumer, $data, $creditApplied);
        }

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

    /**
     * Cash or bank-transfer-with-screenshot, chosen before the job is done.
     * Bank transfer is verified into escrow by an admin, same as any other
     * prepaid gateway. Cash can't really be "prepaid" — it's recorded as a
     * pending commitment and only settled once the job is actually marked
     * complete (see BookingObserver).
     */
    private function storeCashOrBankTransfer(
        Request $request,
        Booking $booking,
        \App\Models\User $consumer,
        array $data,
        float $creditApplied
    ): JsonResponse {
        $payment = Payment::create([
            'reference' => $this->generateReference(),
            'booking_id' => $booking->id,
            'consumer_id' => $consumer->id,
            'gateway' => $data['gateway'],
            'amount' => $booking->price,
            'credit_applied' => $creditApplied,
            'status' => Payment::STATUS_PENDING,
        ]);

        if ($data['gateway'] === Payment::GATEWAY_CASH) {
            app(\App\Services\Notifier::class)->notify(
                $booking->providerProfile->user,
                'payment',
                'Customer will pay in cash',
                'The customer chose to pay booking ' . $booking->reference . ' in cash once the job is done.',
                route('provider.bookings.show', $booking)
            );

            return response()->json([
                'message' => 'Got it — pay the provider in cash once the job is done.',
                'payment' => new PaymentResource($payment->fresh()),
            ], 201);
        }

        $path = $request->file('screenshot')->store('payment-screenshots', 'public');
        $payment->update(['screenshot_path' => $path]);

        app(\App\Services\Notifier::class)->notifyAdmins(
            'payment',
            'Payment awaiting verification',
            'Bank transfer for booking ' . $booking->reference . ' needs verification (Rs. ' . number_format((float) $booking->price, 0) . ').',
            route('admin.payments.show', $payment)
        );

        return response()->json([
            'message' => "Thanks — we'll confirm your transfer shortly.",
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
