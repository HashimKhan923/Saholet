<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'frequency_months' => $this->frequency_months,
            'frequency_label' => $this->frequencyLabel(),
            'total_visits' => $this->total_visits,
            'price_per_visit' => (float) $this->price_per_visit,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
