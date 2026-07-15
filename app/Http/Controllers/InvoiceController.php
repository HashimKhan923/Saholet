<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function bookingReceipt(Request $request, Booking $booking): Response
    {
        $this->authorize('viewReceipt', $booking);

        $booking->load(['service.category', 'providerProfile.user', 'consumer']);
        $payment = $booking->activePayment();

        abort_unless($payment && in_array($payment->status, ['escrow', 'released'], true), 404);

        $pdf = Pdf::loadView('invoices.receipt', [
            'title' => 'Booking Receipt',
            'reference' => $booking->reference,
            'date' => $booking->confirmed_at ?? $booking->created_at,
            'billTo' => [
                'name' => $booking->consumer->name,
                'email' => $booking->consumer->email,
                'phone' => $booking->consumer->phone,
                'address' => $booking->address,
            ],
            'from' => $booking->providerProfile->business_name ?: $booking->providerProfile->user->name,
            'lineItems' => [
                [
                    'description' => $booking->service->name . ($booking->notes ? ' — ' . $booking->notes : ''),
                    'qty' => 1,
                    'unitPrice' => (float) $booking->price,
                    'total' => (float) $booking->price,
                ],
            ],
            'total' => (float) $booking->price,
            'paymentInfo' => [
                'gateway' => ucfirst($payment->gateway),
                'reference' => $payment->gateway_reference,
                'paidAt' => $payment->paid_at,
                'status' => $payment->isReleased() ? 'Paid & released' : 'Paid (held in escrow)',
            ],
        ]);

        return $pdf->download("receipt-{$booking->reference}.pdf");
    }

    public function contractReceipt(Request $request, Contract $contract): Response
    {
        $this->authorize('viewReceipt', $contract);

        abort_unless(in_array($contract->status, [
            Contract::STATUS_ACCEPTED, Contract::STATUS_IN_PROGRESS, Contract::STATUS_COMPLETED,
        ], true), 404);

        $contract->load(['consumer', 'items.service', 'milestones.payment']);

        $lineItems = $contract->items->map(fn ($item) => [
            'description' => $item->service->name . ($item->notes ? ' — ' . $item->notes : ''),
            'qty' => $item->quantity,
            'unitPrice' => (float) ($item->quoted_price ?? 0) / max($item->quantity, 1),
            'total' => (float) ($item->quoted_price ?? 0),
        ])->all();

        $paidMilestones = $contract->milestones->whereIn('status', ['escrow', 'released']);

        $pdf = Pdf::loadView('invoices.receipt', [
            'title' => 'Contract Invoice',
            'reference' => $contract->reference,
            'date' => $contract->accepted_at ?? $contract->created_at,
            'billTo' => [
                'name' => $contract->consumer->name,
                'email' => $contract->consumer->email,
                'phone' => $contract->consumer->phone,
                'address' => $contract->address,
            ],
            'from' => config('app.name'),
            'lineItems' => $lineItems,
            'total' => (float) ($contract->quoted_total ?? 0),
            'paymentInfo' => [
                'gateway' => null,
                'reference' => null,
                'paidAt' => null,
                'status' => $paidMilestones->count() . ' of ' . $contract->milestones->count() . ' milestones paid — Rs. '
                    . number_format((float) $paidMilestones->sum('amount'), 0) . ' of Rs. ' . number_format((float) $contract->milestones->sum('amount'), 0),
            ],
        ]);

        return $pdf->download("invoice-{$contract->reference}.pdf");
    }
}
