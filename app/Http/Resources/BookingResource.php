<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'provider' => new ProviderProfileResource($this->whenLoaded('providerProfile')),
            'consumer' => $this->when(
                $this->relationLoaded('consumer'),
                fn () => [
                    'id' => $this->consumer?->id,
                    'name' => $this->consumer?->name,
                    'phone' => $this->consumer?->phone,
                ]
            ),
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'scheduled_time' => $this->scheduled_time,
            'price' => (float) $this->price,
            'duration_minutes' => $this->duration_minutes,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'notes' => $this->notes,
            'cancelled_by' => $this->cancelled_by,
            'cancellation_reason' => $this->cancellation_reason,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'review' => new ReviewResource($this->whenLoaded('review')),
            'dispute' => new DisputeResource($this->whenLoaded('dispute')),
            'permissions' => $user ? [
                'can_cancel' => $this->canBeCancelledByConsumer(),
                'is_payable' => $this->isPayable(),
                'is_reviewable' => $this->isReviewable(),
                'is_disputable' => $this->isDisputable(),
                'is_communicable' => $this->isCommunicable(),
                'can_share_location' => $this->canShareLocation(),
                'is_provider' => $this->isProviderUser($user),
            ] : null,
        ];
    }
}
