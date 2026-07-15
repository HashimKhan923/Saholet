<?php

namespace App\Services;

use App\Models\ServiceArea;
use App\Models\Setting;

class GeofenceService
{
    public function isEnabled(): bool
    {
        return Setting::bool('geofencing_enabled', false);
    }

    /** @return array<int, string> lowercased active city names */
    public function activeCities(): array
    {
        return ServiceArea::active()
            ->pluck('city')
            ->map(fn ($c) => mb_strtolower(trim((string) $c)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** True if bookings for the given city are allowed. */
    public function isAllowed(?string $city): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        $cities = $this->activeCities();

        // Enabled but nothing configured yet → don't block anyone.
        if (empty($cities)) {
            return true;
        }

        if (blank($city)) {
            return false;
        }

        return in_array(mb_strtolower(trim($city)), $cities, true);
    }
}