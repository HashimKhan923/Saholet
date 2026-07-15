<?php

namespace App\Events;

use App\Models\TrackingUpdate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TrackingUpdate $update) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('booking.' . $this->update->booking_id)];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'latitude' => (float) $this->update->latitude,
            'longitude' => (float) $this->update->longitude,
            'note' => $this->update->note,
            'time' => $this->update->created_at->format('d M, g:i A'),
        ];
    }
}