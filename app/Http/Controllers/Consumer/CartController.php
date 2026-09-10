<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Services\CouponService;
use App\Services\ShippingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private ShippingCalculator $shipping, private CouponService $coupons) {}

    public function index(Request $request): View
    {
        $cart = $this->cartFor($request);
        $groups = $this->summarize($cart, $request->user());

        return view('cart.index', compact('cart', 'groups'));
    }

    /** Body: product_id, quantity (default 1). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        abort_unless($product->isPurchasable(), 422, 'This product is not currently available.');

        $cart = $this->cartFor($request);
        $quantity = $data['quantity'] ?? 1;

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = min(($item->exists ? $item->quantity : 0) + $quantity, $product->stock_quantity);
        $item->save();

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($item->cart_id === $cart->id, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $item->update(['quantity' => min($data['quantity'], $item->product->stock_quantity)]);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($item->cart_id === $cart->id, 404);

        $item->delete();

        return back()->with('success', 'Removed from cart.');
    }

    /** Body: provider_profile_id, code. */
    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_profile_id' => ['required', 'integer', 'exists:provider_profiles,id'],
            'code' => ['required', 'string', 'max:32'],
        ]);

        $coupon = $this->coupons->findUsable($data['provider_profile_id'], $data['code'], $request->user());

        if (! $coupon) {
            return back()->with('error', 'That coupon code is invalid, expired, or already used.');
        }

        $this->coupons->apply($data['provider_profile_id'], $coupon->code);

        return back()->with('success', 'Coupon applied: ' . $coupon->label() . '.');
    }

    public function removeCoupon(Request $request, ProviderProfile $provider): RedirectResponse
    {
        $this->coupons->remove($provider->id);

        return back()->with('success', 'Coupon removed.');
    }

    private function cartFor(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    /** Cart items grouped by provider, each with its own subtotal + shipping estimate + any applied coupon — the shape checkout will split into orders. */
    private function summarize(Cart $cart, $user): Collection
    {
        return $cart->itemsByProvider()->map(function (Collection $items) use ($user) {
            $provider = $items->first()->product->providerProfile;
            $subtotal = round($items->sum(fn (CartItem $item) => $item->lineTotal()), 2);
            $coupon = $this->coupons->appliedFor($provider->id, $user);
            $discount = $coupon ? $coupon->discountFor($subtotal) : 0.0;

            return [
                'provider' => $provider,
                'items' => $items,
                'subtotal' => $subtotal,
                'coupon' => $coupon,
                'discount' => $discount,
                'delivery_estimate' => $provider->offersDelivery() ? $this->shipping->costFor($provider, $subtotal - $discount) : null,
                'pickup_available' => $provider->pickup_enabled,
            ];
        })->values();
    }
}
