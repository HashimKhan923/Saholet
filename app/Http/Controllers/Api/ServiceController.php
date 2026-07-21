<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /** All active services, e.g. for search/typeahead. */
    public function index(): JsonResponse
    {
        $services = Service::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['services' => ServiceResource::collection($services)]);
    }

    /** A service's detail plus the approved providers offering it, cheapest first. */
    public function show(Service $service): JsonResponse
    {
        abort_unless($service->is_active, 404);

        $service->load('category');

        $providers = ProviderService::with('providerProfile.user')
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->whereHas('providerProfile', fn ($q) => $q->where('status', 'approved'))
            ->orderBy('price')
            ->paginate(15);

        $related = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(3)
            ->get();

        return response()->json([
            'service' => new ServiceResource($service),
            'providers' => $providers->getCollection()->map(fn (ProviderService $ps) => [
                'provider_profile_id' => $ps->provider_profile_id,
                'provider' => new \App\Http\Resources\ProviderProfileResource($ps->providerProfile),
                'price' => (float) $ps->price,
            ])->values(),
            'providers_pagination' => [
                'current_page' => $providers->currentPage(),
                'last_page' => $providers->lastPage(),
                'total' => $providers->total(),
            ],
            'related_services' => ServiceResource::collection($related),
        ]);
    }
}
