<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WithdrawalRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'payout_method' => $this->payout_method,
            'method_label' => $this->methodLabel(),
            'admin_notes' => $this->admin_notes,
            'screenshot_url' => $this->screenshot_path ? Storage::disk('public')->url($this->screenshot_path) : null,
            'processed_at' => $this->processed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
