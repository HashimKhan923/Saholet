<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout, private CouponService $coupons) {}

    public function show(Request $request): View
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $groups = $cart->itemsByProvider();
        abort_if($groups->isEmpty(), 404);

        $addresses = $request->user()->addresses()->get();
        $companyAccount = config('payments.company_account');

        $appliedCoupons = $groups->mapWithKeys(
            fn ($items, $providerId) => [$providerId => $this->coupons->appliedFor((int) $providerId, $request->user())]
        );

        return view('checkout.index', compact('cart', 'groups', 'addresses', 'companyAccount', 'appliedCoupons'));
    }

    /** Body: orders[] — one entry per provider group, see CheckoutService::checkout(). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'orders' => ['required', 'array', 'min:1'],
            'orders.*.provider_profile_id' => ['required', 'integer', 'exists:provider_profiles,id'],
            'orders.*.fulfillment_method' => ['required', Rule::in([Order::FULFILLMENT_DELIVERY, Order::FULFILLMENT_PICKUP])],
            'orders.*.payment_method' => ['required', Rule::in([Payment::GATEWAY_CASH, Payment::GATEWAY_BANK_TRANSFER])],
            'orders.*.address_id' => ['required_if:orders.*.fulfillment_method,delivery', 'nullable', 'integer', 'exists:addresses,id'],
            'orders.*.screenshot' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192',
            ],
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $selections = [];

        foreach ($data['orders'] as $index => $entry) {
            $selection = [
                'provider_profile_id' => $entry['provider_profile_id'],
                'fulfillment_method' => $entry['fulfillment_method'],
                'payment_method' => $entry['payment_method'],
                'coupon_code' => $this->coupons->appliedFor((int) $entry['provider_profile_id'], $request->user())?->code,
            ];

            if ($entry['fulfillment_method'] === Order::FULFILLMENT_DELIVERY) {
                $address = Address::where('id', $entry['address_id'])->where('user_id', $request->user()->id)->first();
                abort_unless($address, 422, 'Choose a valid delivery address.');

                $selection['address_id'] = $address->id;
                $selection['shipping_address'] = $address->address;
                $selection['shipping_city'] = $address->city;
                $selection['shipping_lat'] = $address->latitude;
                $selection['shipping_lng'] = $address->longitude;
            }

            if ($entry['payment_method'] === Payment::GATEWAY_BANK_TRANSFER) {
                $file = $request->file("orders.{$index}.screenshot");
                abort_unless($file, 422, 'Upload a screenshot of your bank transfer.');
                $selection['screenshot_path'] = $file->store('payment-screenshots', 'public');
            }

            $selections[] = $selection;
        }

        $orders = $this->checkout->checkout($request->user(), $cart, $selections);

        return redirect()->route('consumer.orders.index')
            ->with('success', $orders->count() > 1
                ? 'Placed ' . $orders->count() . ' orders — one per shop.'
                : 'Order placed.');
    }
}
