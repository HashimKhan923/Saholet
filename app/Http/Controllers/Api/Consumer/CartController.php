<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\ProviderProfileResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CartController extends Controller
{
    public function __construct(private ShippingCalculator $shipping, private CouponService $coupons) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartFor($request);

        return response()->json(['groups' => $this->summarize($cart)]);
    }

    /** Body: product_id, quantity (default 1). */
    public function store(Request $request): JsonResponse
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

        return response()->json(['groups' => $this->summarize($cart)], 201);
    }

    public function update(Request $request, CartItem $item): JsonResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($item->cart_id === $cart->id, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $item->update(['quantity' => min($data['quantity'], $item->product->stock_quantity)]);

        return response()->json(['groups' => $this->summarize($cart)]);
    }

    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($item->cart_id === $cart->id, 404);

        $item->delete();

        return response()->json(['groups' => $this->summarize($cart)]);
    }

    /**
     * Stateless coupon check — the mobile app holds the "applied" code itself
     * (a bearer-token API request has no session to remember it in) and
     * re-sends coupon_code directly on the checkout call; this just lets it
     * validate + preview the discount before that.
     * Body: provider_profile_id, code.
     */
    public function previewCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider_profile_id' => ['required', 'integer', 'exists:provider_profiles,id'],
            'code' => ['required', 'string', 'max:32'],
        ]);

        $coupon = $this->coupons->findUsable($data['provider_profile_id'], $data['code'], $request->user());

        if (! $coupon) {
            return response()->json(['valid' => false, 'message' => 'That coupon code is invalid, expired, or already used.'], 422);
        }

        $cart = $this->cartFor($request);
        $group = $this->summarize($cart)->firstWhere('provider.id', $data['provider_profile_id']);
        $subtotal = $group['subtotal'] ?? 0.0;

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'label' => $coupon->label(),
            'discount' => $coupon->discountFor($subtotal),
        ]);
    }

    private function cartFor(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function summarize(Cart $cart): Collection
    {
        return $cart->itemsByProvider()->map(function (Collection $items) {
            $provider = $items->first()->product->providerProfile;
            $subtotal = round($items->sum(fn (CartItem $item) => $item->lineTotal()), 2);

            return [
                'provider' => new ProviderProfileResource($provider),
                'items' => CartItemResource::collection($items),
                'subtotal' => $subtotal,
                'delivery_estimate' => $provider->offersDelivery() ? $this->shipping->costFor($provider, $subtotal) : null,
                'pickup_available' => (bool) $provider->pickup_enabled,
            ];
        })->values();
    }
}
