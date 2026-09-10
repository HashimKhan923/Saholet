<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderProfileResource;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ShopSettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        return response()->json(['provider' => new ProviderProfileResource($profile)]);
    }

    /** Body: shipping_type (flat|percentage|free|null), shipping_flat_rate, shipping_percentage, pickup_enabled. */
    public function update(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'shipping_type' => ['nullable', Rule::in(['flat', 'percentage', 'free'])],
            'shipping_flat_rate' => ['required_if:shipping_type,flat', 'nullable', 'numeric', 'min:0', 'max:99999'],
            'shipping_percentage' => ['required_if:shipping_type,percentage', 'nullable', 'numeric', 'min:0', 'max:100'],
            'pickup_enabled' => ['nullable', 'boolean'],
            'pickup_hours' => ['nullable', 'string', 'max:255'],
        ]);

        $profile->update([
            'shipping_type' => $data['shipping_type'] ?: null,
            'shipping_flat_rate' => $data['shipping_type'] === 'flat' ? $data['shipping_flat_rate'] : null,
            'shipping_percentage' => $data['shipping_type'] === 'percentage' ? $data['shipping_percentage'] : null,
            'pickup_enabled' => $request->boolean('pickup_enabled'),
            'pickup_hours' => $request->boolean('pickup_enabled') ? ($data['pickup_hours'] ?? null) : null,
        ]);

        return response()->json(['provider' => new ProviderProfileResource($profile->fresh())]);
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
