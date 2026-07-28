<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
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
