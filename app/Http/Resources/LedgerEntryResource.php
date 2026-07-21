<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bucket' => $this->bucket,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'booking_reference' => $this->whenLoaded('payment', fn () => $this->payment?->booking?->reference),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
