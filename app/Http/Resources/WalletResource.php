<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'available_balance' => (float) $this->available_balance,
            'escrow_balance' => (float) $this->escrow_balance,
        ];
    }
}
