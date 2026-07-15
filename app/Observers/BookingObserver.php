<?php

namespace App\Observers;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;

class BookingObserver
{
    public function updated(Booking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        try {
            broadcast(new BookingStatusUpdated($booking));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
