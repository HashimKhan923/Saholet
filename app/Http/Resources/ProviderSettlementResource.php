<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderSettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'method' => $this->method,
            'method_label' => $this->methodLabel(),
            'amount' => (float) $this->amount,
            'confirmed_amount' => $this->confirmed_amount !== null ? (float) $this->confirmed_amount : null,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
