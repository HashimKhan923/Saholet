<?php

namespace App\Events;

use App\Models\EmergencyRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyRequestCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @param int[] $providerProfileIds Matched providers this request was dispatched to — each gets it privately on their own channel. */
    public function __construct(public EmergencyRequest $emergency, public array $providerProfileIds) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return array_map(
            fn (int $profileId) => new PrivateChannel('provider.' . $profileId),
            $this->providerProfileIds
        );
    }

    public function broadcastAs(): string
    {
        return 'emergency.created';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->emergency->id,
            'reference' => $this->emergency->reference,
            'service' => $this->emergency->service?->name,
            'city' => $this->emergency->city,
            'address' => $this->emergency->address,
        ];
    }
}
