<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->profileFor($request);
        $coupons = $profile->coupons()->withCount('redemptions')->latest()->paginate(15)->withQueryString();

        return view('provider.coupons.index', compact('coupons'));
    }

    /** Body: code, type (flat|percentage), value, expires_at?. */
    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $data = $this->validateData($request, $profile);

        $profile->coupons()->create([
            ...$data,
            'code' => strtoupper($data['code']),
        ]);

        return back()->with('success', 'Coupon created.');
    }

    public function toggleActive(Request $request, Coupon $coupon): RedirectResponse
    {
        $this->profileFor($request);
        $this->authorize('update', $coupon);

        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.');
    }

    public function destroy(Request $request, Coupon $coupon): RedirectResponse
    {
        $this->profileFor($request);
        $this->authorize('delete', $coupon);

        $coupon->delete();

        return back()->with('success', 'Coupon removed.');
    }

    private function validateData(Request $request, ProviderProfile $profile, ?Coupon $coupon = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:32', 'alpha_num',
                Rule::unique('coupons')->where('provider_profile_id', $profile->id)->ignore($coupon?->id),
            ],
            'type' => ['required', Rule::in([Coupon::TYPE_FLAT, Coupon::TYPE_PERCENTAGE])],
            'value' => [
                'required', 'numeric', 'min:0.01',
                Rule::when(fn ($input) => $input['type'] === Coupon::TYPE_PERCENTAGE, ['max:100']),
            ],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
