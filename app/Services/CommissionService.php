<?php

namespace App\Services;

use App\Contracts\Payable;

class CommissionService
{
    public const DEFAULT_RATE = 10.0;

    /** Resolve the commission percent for whatever a payment is actually tied to. */
    public function rateFor(Payable $payable): float
    {
        return $payable->commissionRate();
    }

    /**
     * @return array{rate: float, commission: float, provider: float}
     */
    public function compute(float $amount, float $rate): array
    {
        $rate = max(0, min(100, $rate));
        $commission = round($amount * $rate / 100, 2);
        $provider = round($amount - $commission, 2);

        return ['rate' => $rate, 'commission' => $commission, 'provider' => $provider];
    }
}