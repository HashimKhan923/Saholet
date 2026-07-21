<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'city' => $this->city,
            'preferred_start_date' => $this->preferred_start_date?->toDateString(),
            'status' => $this->status,
            'quoted_total' => $this->quoted_total !== null ? (float) $this->quoted_total : null,
            'items' => ContractItemResource::collection($this->whenLoaded('items')),
            'photos' => ContractPhotoResource::collection($this->whenLoaded('photos')),
            'milestones' => ContractMilestoneResource::collection($this->whenLoaded('milestones')),
            'permissions' => [
                'is_quoted' => $this->isQuoted(),
                'is_accepted' => $this->isAccepted(),
                'is_cancellable' => $this->isCancellable(),
            ],
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
