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

    /**
     * True if a submission at this location is allowed. Purely boundary-based —
     * a location is allowed only if it falls inside a drawn polygon on an
     * active service area. No coordinates, or no match against any active
     * boundary, means outside.
     */
    public function isAllowed(?float $lat = null, ?float $lng = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        $areas = ServiceArea::active()->get();

        // Enabled but nothing configured yet → don't block anyone.
        if ($areas->isEmpty()) {
            return true;
        }

        if ($lat === null || $lng === null) {
            return false;
        }

        foreach ($areas as $area) {
            if ($area->hasBoundary() && $this->pointInPolygon($lat, $lng, $area->boundary)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Standard ray-casting point-in-polygon test. $boundary is an array of
     * ['lat' => float, 'lng' => float] vertices (as drawn on the admin map).
     */
    private function pointInPolygon(float $lat, float $lng, array $boundary): bool
    {
        $vertices = array_values(array_filter(
            $boundary,
            fn ($v) => is_array($v) && isset($v['lat'], $v['lng'])
        ));

        $count = count($vertices);
        if ($count < 3) {
            return false;
        }

        $inside = false;
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $latI = (float) $vertices[$i]['lat'];
            $lngI = (float) $vertices[$i]['lng'];
            $latJ = (float) $vertices[$j]['lat'];
            $lngJ = (float) $vertices[$j]['lng'];

            $intersects = ($lngI > $lng) !== ($lngJ > $lng)
                && $lat < ($latJ - $latI) * ($lng - $lngI) / ($lngJ - $lngI) + $latI;

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
