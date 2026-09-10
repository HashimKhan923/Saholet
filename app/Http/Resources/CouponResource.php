<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => (float) $this->value,
            'label' => $this->label(),
            'is_active' => (bool) $this->is_active,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'redemptions_count' => $this->whenCounted('redemptions'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
