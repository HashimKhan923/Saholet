<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'consumer_name' => $this->whenLoaded('consumer', fn () => $this->consumer?->name),
            'service_name' => $this->whenLoaded('service', fn () => $this->service?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
