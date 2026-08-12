<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Commission breakdown is between the platform and the provider — a
        // consumer paying for their own booking has no reason to see it.
        $showCommission = $request->user()?->isAdmin() || $request->user()?->isProvider();

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'gateway' => $this->gateway,
            'amount' => (float) $this->amount,
            'credit_applied' => (float) $this->credit_applied,
            'commission_rate' => $showCommission && $this->commission_rate !== null ? (float) $this->commission_rate : null,
            'commission_amount' => $showCommission && $this->commission_amount !== null ? (float) $this->commission_amount : null,
            'provider_amount' => $showCommission && $this->provider_amount !== null ? (float) $this->provider_amount : null,
            'status' => $this->status,
            'screenshot_url' => $this->screenshot_path ? Storage::disk('public')->url($this->screenshot_path) : null,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'released_at' => $this->released_at?->toIso8601String(),
            'refunded_at' => $this->refunded_at?->toIso8601String(),
        ];
    }
}
