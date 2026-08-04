<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'consumer' => $this->when(
                $this->relationLoaded('consumer'),
                fn () => ['id' => $this->consumer?->id, 'name' => $this->consumer?->name, 'phone' => $this->consumer?->phone]
            ),
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'city' => $this->city,
            'notes' => $this->notes,
            'quoted_price' => $this->quoted_price !== null ? (float) $this->quoted_price : null,
            'quoted_at' => $this->quoted_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'declined_at' => $this->declined_at?->toIso8601String(),
            'booking_id' => $this->booking_id,
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'matched_provider' => new ProviderProfileResource($this->whenLoaded('matchedProvider')),
            'matched_at' => $this->matched_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            // Present only on the provider's board listing, when annotated in the controller.
            'my_price' => $this->my_price ?? null,
        ];
    }
}
