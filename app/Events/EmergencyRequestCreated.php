<?php

namespace App\Events;

use App\Models\EmergencyRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyRequestStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public EmergencyRequest $emergency) {}

    /**
     * Public: carries only an id and a status — no address, no consumer identity.
     * Every provider staring at the board needs this the instant a request is taken.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('emergencies')];
    }

    public function broadcastAs(): string
    {
        return 'emergency.status.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id'     => $this->emergency->id,
            'status' => $this->emergency->status,
        ];
    }
}