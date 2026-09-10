<?php

namespace App\Services;

use App\Models\ProviderProfile;

class ShippingCalculator
{
    /**
     * Delivery cost for this provider's slice of a cart, or null if this
     * provider doesn't offer delivery at all — the checkout should not
     * offer delivery for this provider in that case.
     */
    public function costFor(ProviderProfile $provider, float $subtotal): ?float
    {
        return match ($provider->shipping_type) {
            'flat' => (float) ($provider->shipping_flat_rate ?? 0),
            'percentage' => round($subtotal * (float) ($provider->shipping_percentage ?? 0) / 100, 2),
            'free' => 0.0,
            default => null,
        };
    }
}
