<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractMilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'is_payable' => $this->isPayable(),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'released_at' => $this->released_at?->toIso8601String(),
            'refunded_at' => $this->refunded_at?->toIso8601String(),
        ];
    }
}
