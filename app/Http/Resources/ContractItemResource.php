<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'quoted_price' => $this->quoted_price !== null ? (float) $this->quoted_price : null,
            'status' => $this->status,
            'provider' => new ProviderProfileResource($this->whenLoaded('providerProfile')),
            'booking_id' => $this->booking_id,
        ];
    }
}
