<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);
        $coupons = $profile->coupons()->withCount('redemptions')->latest()->paginate(15);

        return response()->json([
            'coupons' => CouponResource::collection($coupons->getCollection()),
            'pagination' => ['current_page' => $coupons->currentPage(), 'last_page' => $coupons->lastPage(), 'total' => $coupons->total()],
        ]);
    }

    /** Body: code, type (flat|percentage), value, expires_at?. */
    public function store(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        $data = $this->validateData($request, $profile);

        $coupon = $profile->coupons()->create([
            ...$data,
            'code' => strtoupper($data['code']),
        ]);

        return response()->json(['coupon' => new CouponResource($coupon)], 201);
    }

    public function toggleActive(Request $request, Coupon $coupon): JsonResponse
    {
        $this->profileFor($request);
        $this->authorize('update', $coupon);

        $coupon->update(['is_active' => ! $coupon->is_active]);

        return response()->json(['coupon' => new CouponResource($coupon->fresh())]);
    }

    public function destroy(Request $request, Coupon $coupon): JsonResponse
    {
        $this->profileFor($request);
        $this->authorize('delete', $coupon);

        $coupon->delete();

        return response()->json(['message' => 'Coupon removed.']);
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
