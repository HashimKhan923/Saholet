<?php

namespace App\Services;

use App\Mail\InvoicePaidMail;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
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

    /** Email the booking's invoice to the customer once a payment is confirmed. */
    public function emailPaymentConfirmation(Booking $booking, Payment $payment): void
    {
        $invoice = $this->findForBooking($booking) ?? $this->createForBooking($booking);

        if (! $booking->consumer?->email) {
            return;
        }

        Mail::to($booking->consumer->email)->send(new InvoicePaidMail($invoice, $booking, $payment));
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
