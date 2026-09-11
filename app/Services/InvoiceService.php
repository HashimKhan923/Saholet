<?php

namespace App\Services;

use App\Mail\InvoicePaidMail;
use App\Mail\OrderInvoicePaidMail;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    /** The invoice tied to a booking, if one already exists (created on completion). */
    public function findForBooking(Booking $booking): ?Invoice
    {
        return Invoice::where('invoiceable_type', Booking::class)
            ->where('invoiceable_id', $booking->id)
            ->latest()
            ->first();
    }

    /** The invoice tied to a product order, if one already exists (created once payment is finalized). */
    public function findForOrder(Order $order): ?Invoice
    {
        return Invoice::where('invoiceable_type', Order::class)
            ->where('invoiceable_id', $order->id)
            ->latest()
            ->first();
    }

    /** Email the order's invoice to the customer once its payment is confirmed. */
    public function emailOrderPaymentConfirmation(Order $order, Payment $payment): void
    {
        $invoice = $this->findForOrder($order) ?? $this->createForOrder($order);

        if (! $order->consumer?->email) {
            return;
        }

        // Best-effort: an SMTP hiccup here must never roll back the order completion
        // (and its commission/ledger writes) that this runs inside of.
        try {
            Mail::to($order->consumer->email)->send(new OrderInvoicePaidMail($invoice, $order, $payment));
        } catch (\Throwable $e) {
            report($e);
            Log::warning('[invoice] failed to email order invoice', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    public function createForOrder(Order $order): Invoice
    {
        $order->loadMissing(['items', 'consumer']);

        return DB::transaction(function () use ($order) {
            $invoice = Invoice::create([
                'type' => Invoice::TYPE_INVOICE,
                'reference' => Invoice::generateReference(Invoice::TYPE_INVOICE),
                'invoiceable_type' => Order::class,
                'invoiceable_id' => $order->id,
                'consumer_id' => $order->consumer_id,
                'bill_to_name' => $order->consumer->name,
                'bill_to_email' => $order->consumer->email,
                'bill_to_phone' => $order->consumer->phone,
                'bill_to_address' => $order->shipping_address,
                'total' => $order->total_amount,
            ]);

            foreach ($order->items as $index => $item) {
                $invoice->items()->create([
                    'description' => $item->product_name . ' x' . $item->quantity,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->line_total,
                    'sort_order' => $index + 1,
                ]);
            }

            if ((float) $order->shipping_amount > 0) {
                $invoice->items()->create([
                    'description' => 'Shipping',
                    'quantity' => 1,
                    'unit_price' => $order->shipping_amount,
                    'total' => $order->shipping_amount,
                    'sort_order' => $order->items->count() + 1,
                ]);
            }

            return $invoice;
        });
    }

    /** Email the booking's invoice to the customer once a payment is confirmed. */
    public function emailPaymentConfirmation(Booking $booking, Payment $payment): void
    {
        $invoice = $this->findForBooking($booking) ?? $this->createForBooking($booking);

        if (! $booking->consumer?->email) {
            return;
        }

        // Best-effort: an SMTP hiccup here must never roll back the booking payment
        // release (and its ledger writes) that this runs inside of.
        try {
            Mail::to($booking->consumer->email)->send(new InvoicePaidMail($invoice, $booking, $payment));
        } catch (\Throwable $e) {
            report($e);
            Log::warning('[invoice] failed to email booking invoice', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }

    public function createForBooking(Booking $booking): Invoice
    {
        $booking->loadMissing(['service', 'consumer']);

        return DB::transaction(function () use ($booking) {
            $invoice = Invoice::create([
                'type' => Invoice::TYPE_INVOICE,
                'reference' => Invoice::generateReference(Invoice::TYPE_INVOICE),
                'invoiceable_type' => Booking::class,
                'invoiceable_id' => $booking->id,
                'consumer_id' => $booking->consumer_id,
                'bill_to_name' => $booking->consumer->name,
                'bill_to_email' => $booking->consumer->email,
                'bill_to_phone' => $booking->consumer->phone,
                'bill_to_address' => $booking->address,
                'total' => $booking->price,
                'notes' => $booking->notes,
            ]);

            $invoice->items()->create([
                'description' => $booking->service->name . ' (' . $booking->reference . ')',
                'quantity' => 1,
                'unit_price' => $booking->price,
                'total' => $booking->price,
                'sort_order' => 1,
            ]);

            return $invoice;
        });
    }
}
