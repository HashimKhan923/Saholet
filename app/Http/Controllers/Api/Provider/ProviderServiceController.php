<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderServiceResource;
use App\Http\Resources\ServiceResource;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProviderServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return response()->json(['offered' => [], 'available' => []]);
        }

        $offered = $profile->providerServices()->with('service.category')->get()
            ->sortBy(fn (ProviderService $ps) => $ps->service->name)
            ->values();

        $offeredIds = $offered->pluck('service_id')->all();

        $available = Service::with('category')
            ->where('is_active', true)
            ->whereNotIn('id', $offeredIds)
            ->orderBy('name')
            ->get();

        $bookingCounts = Booking::where('provider_profile_id', $profile->id)
            ->selectRaw('service_id, COUNT(*) as aggregate')
            ->groupBy('service_id')
            ->pluck('aggregate', 'service_id');

        return response()->json([
            'offered' => ProviderServiceResource::collection($offered),
            'available' => ServiceResource::collection($available),
            'booking_counts' => $bookingCounts,
        ]);
    }

    /** Body: service_id, price. */
    public function store(Request $request): JsonResponse
    {
        $profile = $this->approvedProfile($request);

        $data = $request->validate([
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::unique('provider_services')->where('provider_profile_id', $profile->id),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $providerService = $profile->providerServices()->create([
            'service_id' => $data['service_id'],
            'price' => $data['price'],
            'is_active' => true,
        ]);

        return response()->json(['provider_service' => new ProviderServiceResource($providerService->load('service.category'))], 201);
    }

    /** Body: price, is_active?. */
    public function update(Request $request, ProviderService $providerService): JsonResponse
    {
        $this->approvedProfile($request);
        $this->authorize('update', $providerService);

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $providerService->update([
            'price' => $data['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json(['provider_service' => new ProviderServiceResource($providerService->fresh('service.category'))]);
    }

    public function destroy(Request $request, ProviderService $providerService): JsonResponse
    {
        $this->approvedProfile($request);
        $this->authorize('delete', $providerService);

        $providerService->delete();

        return response()->json(['message' => 'Service removed from your offerings.']);
    }

    private function approvedProfile(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
