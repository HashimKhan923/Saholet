<?php

namespace App\Services;

use App\Models\Booking;

class CommissionService
{
    public const DEFAULT_RATE = 10.0;

    /**
     * Resolve the commission percent for a booking: the provider's own
     * negotiated rate, set by an admin at approval time — a last-resort
     * default only covers a provider somehow left without one.
     */
    public function rateFor(Booking $booking): float
    {
        $booking->loadMissing('providerProfile');

        return $booking->providerProfile?->commission_rate !== null
            ? (float) $booking->providerProfile->commission_rate
            : self::DEFAULT_RATE;
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