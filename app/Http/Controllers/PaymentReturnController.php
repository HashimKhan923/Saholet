<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\PaymentManager;
use App\Services\PaymentFinalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Return/webhook endpoint for redirect-based gateways (JazzCash, EasyPaisa).
 * The gateway itself decides GET vs POST for its callback, so this route
 * accepts both. Every request's signature is verified before any payment
 * state changes — an unsigned or mismatched request is rejected outright,
 * never trusted.
 */
class PaymentReturnController extends Controller
{
    public function __construct(
        private PaymentManager $payments,
        private PaymentFinalizer $finalizer,
    ) {}

    public function handle(Request $request, string $gateway): RedirectResponse
    {
        abort_unless(in_array($gateway, ['jazzcash', 'easypaisa'], true), 404);

        $driver = $this->payments->driver($gateway);
        $fields = $request->all();

        if (! $driver->verify($fields)) {
            Log::warning('[payments:return] signature verification failed', ['gateway' => $gateway, 'fields' => array_keys($fields)]);

            return redirect()->route('home')->with('error', 'We couldn\'t verify that payment response. If money was deducted, contact support with your reference number.');
        }

        $reference = $gateway === 'jazzcash' ? ($fields['pp_TxnRefNo'] ?? null) : ($fields['orderRefNum'] ?? null);
        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            Log::warning('[payments:return] unknown payment reference', ['gateway' => $gateway, 'reference' => $reference]);

            return redirect()->route('home')->with('error', 'We couldn\'t find that payment.');
        }

        $redirectTo = $this->redirectTargetFor($payment);

        // Idempotent: the gateway may call back more than once (return + webhook).
        if (! $payment->isPending()) {
            return redirect()->to($redirectTo)->with('success', 'Payment already processed.');
        }

        $succeeded = $gateway === 'jazzcash'
            ? (($fields['pp_ResponseCode'] ?? null) === '000')
            : (($fields['status'] ?? $fields['responseCode'] ?? null) === '0000' || ($fields['status'] ?? null) === 'SUCCESS');

        if (! $succeeded) {
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return redirect()->to($redirectTo)->with('error', $fields['pp_ResponseMessage'] ?? $fields['responseDesc'] ?? 'Payment was not completed.');
        }

        $gatewayReference = $fields['pp_RetreivalReferenceNo'] ?? $fields['transactionId'] ?? $reference;

        if ($payment->booking_id) {
            $this->finalizer->finalizeBookingPayment($payment, $gatewayReference);
        } else {
            $this->finalizer->finalizeMilestonePayment($payment, $gatewayReference);
        }

        return redirect()->to($redirectTo)->with('success', 'Payment received and held safely in escrow.');
    }

    private function redirectTargetFor(Payment $payment): string
    {
        if ($payment->booking_id) {
            return route('consumer.bookings.show', $payment->booking_id);
        }

        $payment->loadMissing('contractMilestone');

        return route('consumer.contracts.show', $payment->contractMilestone->contract_id);
    }
}
