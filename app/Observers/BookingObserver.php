<?php

namespace App\Observers;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\ContractItem;

class BookingObserver
{
    public function updated(Booking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        // A contract-derived booking finishing its own work doesn't automatically
        // mean the contract is done (other items/milestones may still be open) —
        // it just marks its own item so the contract's completion gate can see it.
        if ($booking->status === Booking::STATUS_COMPLETED && $booking->contract_item_id) {
            $booking->contractItem?->update(['status' => ContractItem::STATUS_COMPLETED]);
        }

        try {
            broadcast(new BookingStatusUpdated($booking));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
