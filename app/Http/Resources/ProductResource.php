<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_profile_id' => $this->provider_profile_id,
            'provider' => new ProviderProfileResource($this->whenLoaded('providerProfile')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price !== null ? (float) $this->discount_price : null,
            'has_discount' => $this->hasDiscount(),
            'discount_percentage' => $this->discountPercentage(),
            'effective_price' => $this->effectivePrice(),
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->isInStock(),
            'sku' => $this->sku,
            'is_active' => (bool) $this->is_active,
            'deactivation_reason' => $this->when(! $this->is_active, $this->deactivation_reason),
            'reactivation_instructions' => $this->when(! $this->is_active, $this->reactivation_instructions),
            'rating_avg' => $this->ratingAvg(),
            'reviews_count' => $this->reviewsCount(),
            'photos' => ProductPhotoResource::collection($this->whenLoaded('photos')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
