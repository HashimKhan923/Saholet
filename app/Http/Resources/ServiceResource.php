<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->when(
                $this->relationLoaded('category'),
                fn () => [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'slug' => $this->category?->slug,
                    'icon' => $this->category?->icon,
                ]
            ),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'base_price' => (float) $this->base_price,
            'duration_minutes' => $this->duration_minutes,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
