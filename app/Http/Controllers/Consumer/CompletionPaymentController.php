<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Cash-on-completion and bank-transfer-with-screenshot — the two payment
 * methods offered once a booking is actually done, instead of prepaying a
 * card gateway before the provider has even shown up (see Booking::
 * needsCompletionPayment()). Kept separate from Consumer\PaymentController,
 * which still drives the pre-payment gateway flow untouched.
 */
class CompletionPaymentController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function create(Booking $booking): View
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user']);

        abort_unless($booking->needsCompletionPayment(), 404);

        $companyAccount = config('payments.company_account');

        return view('consumer.payments.complete', compact('booking', 'companyAccount'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('pay', $booking);

        $booking->load(['service', 'providerProfile.user']);

        if (! $booking->needsCompletionPayment()) {
            return redirect()
                ->route('consumer.bookings.show', $booking)
                ->with('error', 'This booking is not awaiting payment.');
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

            app(\App\Services\Notifier::class)->notify(
                $booking->providerProfile->user,
                'payment',
                'Cash payment recorded',
                'Booking ' . $booking->reference . ' was paid in cash. Commission has been deducted from your wallet.',
                route('provider.wallet.index')
            );

            return redirect()
                ->route('consumer.bookings.show', $booking)
                ->with('success', 'Cash payment recorded. Thanks!');
        }

        $path = $request->file('screenshot')->store('payment-screenshots', 'public');
        $payment->update(['screenshot_path' => $path]);

        app(\App\Services\Notifier::class)->notifyAdmins(
            'payment',
            'Payment awaiting verification',
            'Bank transfer for booking ' . $booking->reference . ' needs verification (Rs. ' . number_format((float) $booking->price, 0) . ').',
            route('admin.payments.show', $payment)
        );

        return redirect()
            ->route('consumer.bookings.show', $booking)
            ->with('success', 'Thanks — we\'ll confirm your transfer shortly.');
    }

    private function generateReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}
