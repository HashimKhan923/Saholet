<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Cash-on-completion and bank-transfer-with-screenshot — the two payment
 * methods offered once a booking is actually done, instead of prepaying a
 * gateway before the provider has even shown up (see Booking::
 * needsCompletionPayment()). Mirrors Consumer\CompletionPaymentController
 * on the web app. Kept separate from Api\Consumer\PaymentController, which
 * still drives the pre-payment gateway flow untouched.
 */
class CompletionPaymentController extends Controller
{
    public function __construct(private WalletService $wallets, private InvoiceService $invoices) {}

    /** What's needed to pay for a just-completed booking: the company bank account details for a transfer. */
    public function options(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user']);

        abort_unless($booking->needsCompletionPayment(), 404);

        return response()->json([
            'amount' => (float) $booking->price,
            'methods' => ['cash', 'bank_transfer'],
            'company_account' => config('payments.company_account'),
        ]);
    }

    /** Body: method (cash|bank_transfer, required), screenshot (required if bank_transfer, multipart). */
    public function store(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user']);

        if (! $booking->needsCompletionPayment()) {
            return response()->json(['message' => 'This booking is not awaiting payment.'], 422);
        }

        $data = $request->validate([
            'method' => ['required', Rule::in([Payment::GATEWAY_CASH, Payment::GATEWAY_BANK_TRANSFER])],
            'screenshot' => [
                Rule::requiredIf($request->input('method') === Payment::GATEWAY_BANK_TRANSFER),
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192',
            ],
        ]);

        $payment = Payment::create([
            'reference' => $this->generateReference(),
            'booking_id' => $booking->id,
            'consumer_id' => $request->user()->id,
            'gateway' => $data['method'],
            'amount' => $booking->price,
            'status' => Payment::STATUS_PENDING,
        ]);

        if ($data['method'] === Payment::GATEWAY_CASH) {
            $this->wallets->chargeCashCommission($payment, $booking->providerProfile->user);
            $this->invoices->emailPaymentConfirmation($booking, $payment);

            app(\App\Services\Notifier::class)->notify(
                $booking->providerProfile->user,
                'payment',
                'Cash payment recorded',
                'Booking ' . $booking->reference . ' was paid in cash. Commission has been deducted from your wallet.',
                route('provider.wallet.index')
            );

            return response()->json([
                'message' => 'Cash payment recorded. Thanks!',
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

    private function generateReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}
