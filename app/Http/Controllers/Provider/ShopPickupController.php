<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShopPickupController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $this->profileFor($request);

        return view('provider.shop-settings.pickup', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'pickup_enabled' => ['nullable', 'boolean'],
            'pickup_hours' => ['nullable', 'string', 'max:255'],
        ]);

        $profile->update([
            'pickup_enabled' => $request->boolean('pickup_enabled'),
            'pickup_hours' => $request->boolean('pickup_enabled') ? ($data['pickup_hours'] ?? null) : null,
        ]);

        return back()->with('success', 'Pickup settings saved.');
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
