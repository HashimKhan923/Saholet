<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\ServiceArea;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;

class ServiceAreaController extends Controller
{
    /** Cities with an approved provider — used to populate city pickers client-side. */
    public function index(): JsonResponse
    {
        $cities = ProviderProfile::approved()->pluck('city')->filter()->unique()->sort()->values();

        return response()->json(['cities' => $cities]);
    }

    /**
     * Active geofence polygons, for shading the map on the address picker.
     * Mirrors the `$activeGeofenceAreas` computation in
     * resources/views/components/address-map-picker.blade.php — same
     * fail-open behavior: geofencing off (or no boundaries drawn) → empty
     * `areas`, nothing shaded or blocked.
     */
    public function geofence(GeofenceService $geofence): JsonResponse
    {
        $enabled = $geofence->isEnabled();

        $areas = $enabled
            ? ServiceArea::active()->get()->filter->hasBoundary()->map(fn (ServiceArea $a) => [
                'name' => $a->name,
                'boundary' => $a->boundary,
            ])->values()
            : collect();

        return response()->json(['enabled' => $enabled, 'areas' => $areas]);
    }
}
