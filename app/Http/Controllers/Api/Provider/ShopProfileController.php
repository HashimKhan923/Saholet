<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderProfileResource;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/** The shop's public identity — name and logo shown on the storefront (shop directory + shop page). */
class ShopProfileController extends Controller
{
    /** Body (multipart when uploading a logo): shop_name, logo, remove_logo. */
    public function update(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'shop_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $updates = ['shop_name' => $data['shop_name'] ?? null];

        if ($request->hasFile('logo')) {
            if ($profile->shop_logo) {
                Storage::disk('public')->delete($profile->shop_logo);
            }
            $updates['shop_logo'] = $request->file('logo')->store('shop-logos', 'public');
        } elseif ($request->boolean('remove_logo') && $profile->shop_logo) {
            Storage::disk('public')->delete($profile->shop_logo);
            $updates['shop_logo'] = null;
        }

        $profile->update($updates);

        return response()->json(['provider' => new ProviderProfileResource($profile->fresh())]);
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
