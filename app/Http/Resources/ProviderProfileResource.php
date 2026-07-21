<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'business_name' => $this->business_name,
            'display_name' => $this->business_name ?: $this->whenLoaded('user', fn () => $this->user?->name),
            'bio' => $this->bio,
            'experience_years' => $this->experience_years,
            'rating_avg' => (float) $this->rating_avg,
            'reviews_count' => (int) $this->reviews_count,
            'city' => $this->city,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'has_payout_method' => $this->hasPayoutMethod(),
            'services' => ProviderServiceResource::collection($this->whenLoaded('providerServices')),
            'portfolio' => ProviderPortfolioPhotoResource::collection($this->whenLoaded('portfolioPhotos')),
        ];
    }
}
