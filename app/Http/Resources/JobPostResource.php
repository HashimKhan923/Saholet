<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostResource extends JsonResource
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
                fn () => ['id' => $this->consumer?->id, 'name' => $this->consumer?->name]
            ),
            'description' => $this->description,
            'budget' => $this->budget !== null ? (float) $this->budget : null,
            'preferred_date' => $this->preferred_date?->toDateString(),
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'city' => $this->city,
            'photos' => JobPostPhotoResource::collection($this->whenLoaded('photos')),
            'bids_count' => $this->whenCounted('bids'),
            'pending_bids_count' => $this->pending_bids_count ?? null,
            'bids' => BidResource::collection($this->whenLoaded('bids')),
            'my_bid' => new BidResource($this->whenLoaded('myBid')),
            'awarded_at' => $this->awarded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
