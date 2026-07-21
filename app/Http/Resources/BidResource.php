<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            'id' => $this->id,
            'job_post_id' => $this->job_post_id,
            'job_post' => $this->when(
                $this->relationLoaded('jobPost'),
                fn () => [
                    'id' => $this->jobPost?->id,
                    'reference' => $this->jobPost?->reference,
                    'status' => $this->jobPost?->status,
                    'service_name' => $this->jobPost?->service?->name,
                ]
            ),
            'provider' => new ProviderProfileResource($this->whenLoaded('providerProfile')),
            'amount' => (float) $this->amount,
            'proposed_date' => $this->proposed_date?->toDateString(),
            'proposed_time' => $this->proposed_time,
            'message' => $this->message,
            'status' => $this->status,
            'booking_id' => $this->booking_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
