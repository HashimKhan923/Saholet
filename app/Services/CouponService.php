<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;

/**
 * A coupon is scoped to one provider and redeemable once per user (see
 * Coupon::alreadyRedeemedBy() / the coupon_redemptions unique index). Cart
 * and checkout keep the applied code per provider in the session
 * ("cart_coupons.{providerId}" => code) so it survives across page loads
 * until checkout actually redeems it or the customer removes it.
 */
class CouponService
{
    private const SESSION_KEY = 'cart_coupons';

    /** Look up + validate a code for a given provider/user — null if not usable for any reason. */
    public function findUsable(int $providerProfileId, string $code, User $user): ?Coupon
    {
        $coupon = Coupon::where('provider_profile_id', $providerProfileId)
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $coupon || ! $coupon->isUsable() || $coupon->alreadyRedeemedBy($user)) {
            return null;
        }

        return $coupon;
    }

    public function apply(int $providerProfileId, string $code): void
    {
        session()->put(self::SESSION_KEY . '.' . $providerProfileId, strtoupper(trim($code)));
    }

    public function remove(int $providerProfileId): void
    {
        session()->forget(self::SESSION_KEY . '.' . $providerProfileId);
    }

    public function clear(int $providerProfileId): void
    {
        $this->remove($providerProfileId);
    }

    /** The coupon currently applied to this provider's slice of the cart, if it's still valid — silently drops a stale/expired one rather than erroring on render. */
    public function appliedFor(int $providerProfileId, User $user): ?Coupon
    {
        $code = session(self::SESSION_KEY . '.' . $providerProfileId);
        if (! $code) {
            return null;
        }

        $coupon = $this->findUsable($providerProfileId, $code, $user);
        if (! $coupon) {
            $this->remove($providerProfileId);
        }

        return $coupon;
    }
}
