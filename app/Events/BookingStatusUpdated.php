<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private const LABELS = [
        Booking::STATUS_PENDING => 'Pending',
        Booking::STATUS_CONFIRMED => 'Confirmed',
        Booking::STATUS_IN_PROGRESS => 'In progress',
        Booking::STATUS_COMPLETED => 'Completed',
        Booking::STATUS_CANCELLED => 'Cancelled',
    ];

    public function __construct(public Booking $booking)
    {
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('booking.' . $this->booking->id)];
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'status' => $this->booking->status,
            'status_label' => self::LABELS[$this->booking->status] ?? ucfirst($this->booking->status),
            'updated_at' => $this->booking->updated_at->format('d M, g:i A'),
        ];
    }
}
