<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Setting;

class CommissionService
{
    public const DEFAULT_RATE = 10.0;

    /** Resolve the commission percent for a booking: category override → global setting → default. */
    public function rateFor(Booking $booking): float
    {
        $booking->loadMissing('service.category');

        $category = $booking->service?->category;

        if ($category && $category->commission_rate !== null) {
            return (float) $category->commission_rate;
        }

        return (float) Setting::get('commission_rate', self::DEFAULT_RATE);
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