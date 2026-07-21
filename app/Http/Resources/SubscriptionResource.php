<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'provider' => new ProviderProfileResource($this->whenLoaded('providerProfile')),
            'address' => $this->address,
            'city' => $this->city,
            'next_visit_date' => $this->next_visit_date?->toDateString(),
            'visits_used' => $this->visits_used,
            'is_cancellable' => $this->isCancellable(),
            'bookings' => BookingResource::collection($this->whenLoaded('bookings')),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
