<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'referral_code' => $this->referral_code,
            'credit_balance' => (float) $this->credit_balance,
            'is_suspended' => $this->isSuspended(),
            'provider_status' => $this->when(
                $this->role === 'provider',
                fn () => $this->providerProfile?->status
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
