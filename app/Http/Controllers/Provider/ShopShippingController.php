<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShopShippingController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $this->profileFor($request);

        return view('provider.shop-settings.shipping', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'shipping_type' => ['nullable', Rule::in(['flat', 'percentage', 'free'])],
            'shipping_flat_rate' => ['required_if:shipping_type,flat', 'nullable', 'numeric', 'min:0', 'max:99999'],
            'shipping_percentage' => ['required_if:shipping_type,percentage', 'nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $profile->update([
            'shipping_type' => $data['shipping_type'] ?: null,
            'shipping_flat_rate' => $data['shipping_type'] === 'flat' ? $data['shipping_flat_rate'] : null,
            'shipping_percentage' => $data['shipping_type'] === 'percentage' ? $data['shipping_percentage'] : null,
        ]);

        return back()->with('success', 'Shipping settings saved.');
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
