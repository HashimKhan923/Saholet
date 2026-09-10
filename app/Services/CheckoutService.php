<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Splits a (possibly multi-provider) cart into one Order per provider at
 * checkout — each carrying its own fulfillment method, shipping cost,
 * coupon, and payment. See app/Contracts/Payable.php for how each Order's
 * commission later flows through the same escrow/wallet engine as a Booking.
 */
class CheckoutService
{
    public function __construct(
        private ShippingCalculator $shipping,
        private CouponService $coupons,
        private Notifier $notifier,
    ) {}

    /**
     * @param array<int, array{
     *     provider_profile_id:int, fulfillment_method:string, payment_method:string,
     *     address_id?:int, shipping_address?:string, shipping_city?:string,
     *     shipping_lat?:float, shipping_lng?:float, screenshot_path?:string, coupon_code?:string,
     * }> $selections One entry per provider group present in the cart.
     * @return Collection<int, Order>
     */
    public function checkout(User $consumer, Cart $cart, array $selections): Collection
    {
        $grouped = $cart->itemsByProvider();

        return DB::transaction(function () use ($consumer, $cart, $grouped, $selections) {
            $orders = collect();

            foreach ($selections as $selection) {
                $items = $grouped->get($selection['provider_profile_id']);
                $this->abortUnless($items && $items->isNotEmpty(), 'No cart items for that provider.');

                $orders->push($this->createOrderForProvider($consumer, $items, $selection));
            }

            CartItem::where('cart_id', $cart->id)
                ->whereIn('product_id', $grouped->flatten(1)->pluck('product_id'))
                ->delete();

            return $orders;
        });
    }

    private function createOrderForProvider(User $consumer, Collection $items, array $selection): Order
    {
        $provider = ProviderProfile::findOrFail($selection['provider_profile_id']);
        $this->abortUnless($provider->isApproved(), 'This provider is no longer available.');

        $fulfillment = $selection['fulfillment_method'];
        $this->abortUnless(
            ($fulfillment === Order::FULFILLMENT_PICKUP && $provider->pickup_enabled)
                || ($fulfillment === Order::FULFILLMENT_DELIVERY && $provider->offersDelivery()),
            'This provider does not offer that fulfillment method.'
        );

        [$lineData, $subtotal] = $this->lockAndPriceItems($items, $provider->id);

        $coupon = null;
        $discount = 0.0;
        if (! empty($selection['coupon_code'])) {
            // Re-validated here regardless of any earlier preview — never trust the client's word for it.
            $coupon = $this->coupons->findUsable($provider->id, $selection['coupon_code'], $consumer);
            $this->abortUnless($coupon !== null, 'That coupon code is invalid, expired, or already used.');
            $discount = $coupon->discountFor($subtotal);
        }

        $discountedSubtotal = round($subtotal - $discount, 2);

        $shippingAmount = 0.0;
        $city = null;

        if ($fulfillment === Order::FULFILLMENT_DELIVERY) {
            $city = $selection['shipping_city'] ?? null;
            $cost = $this->shipping->costFor($provider, $discountedSubtotal);
            $this->abortUnless($cost !== null, 'This provider does not offer delivery.');
            $shippingAmount = $cost;
        }

        $order = Order::create([
            'reference' => $this->generateOrderReference(),
            'consumer_id' => $consumer->id,
            'provider_profile_id' => $provider->id,
            'fulfillment_method' => $fulfillment,
            'address_id' => $selection['address_id'] ?? null,
            'shipping_address' => $fulfillment === Order::FULFILLMENT_DELIVERY ? ($selection['shipping_address'] ?? null) : null,
            'shipping_city' => $city,
            'shipping_lat' => $selection['shipping_lat'] ?? null,
            'shipping_lng' => $selection['shipping_lng'] ?? null,
            'subtotal' => $subtotal,
            'coupon_id' => $coupon?->id,
            'discount_amount' => $discount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $discountedSubtotal + $shippingAmount,
            'payment_method' => $selection['payment_method'],
            'status' => Order::STATUS_PENDING,
        ]);

        foreach ($lineData as $line) {
            $order->items()->create($line);
        }

        if ($coupon) {
            // Unique(coupon_id, user_id) at the DB level is the real "one time per user"
            // guarantee — this insert fails safely under a concurrent double-submit race.
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $consumer->id,
                'order_id' => $order->id,
                'discount_amount' => $discount,
            ]);
            $this->coupons->clear($provider->id);
        }

        $order->events()->create(['type' => OrderEvent::TYPE_CREATED, 'to_status' => Order::STATUS_PENDING]);

        if ($selection['payment_method'] === Payment::GATEWAY_BANK_TRANSFER) {
            Payment::create([
                'reference' => $this->generatePaymentReference(),
                'order_id' => $order->id,
                'consumer_id' => $consumer->id,
                'gateway' => Payment::GATEWAY_BANK_TRANSFER,
                'amount' => $order->total_amount,
                'status' => Payment::STATUS_PENDING,
                'screenshot_path' => $selection['screenshot_path'] ?? null,
            ]);

            $this->notifier->notifyAdmins(
                'order',
                'Order payment awaiting verification',
                'Bank transfer for order ' . $order->reference . ' needs verification (Rs. ' . number_format($order->total_amount, 0) . ').',
                route('admin.orders.show', $order),
            );
        }

        $this->notifier->notify(
            $provider->user,
            'order',
            'New order received',
            'You have a new order (' . $order->reference . ') worth Rs. ' . number_format($order->total_amount, 0) . '.',
            route('provider.orders.show', $order),
        );

        return $order->fresh(['items', 'payments']);
    }

    /**
     * Locks each product row, validates it's still active/purchasable/in
     * stock, decrements stock, and returns the priced order-item rows plus
     * the subtotal — all inside the caller's transaction.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: float}
     */
    private function lockAndPriceItems(Collection $items, int $providerProfileId): array
    {
        $lineData = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

            $this->abortUnless(
                $product && $product->is_active && $product->provider_profile_id === $providerProfileId,
                'A product in your cart is no longer available.'
            );
            $this->abortUnless(
                $product->stock_quantity >= $item->quantity,
                "Not enough stock for {$product->name}."
            );

            $unitPrice = $product->effectivePrice();
            $lineTotal = round($unitPrice * $item->quantity, 2);
            $subtotal += $lineTotal;

            $lineData[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $item->quantity,
                'line_total' => $lineTotal,
            ];

            $product->decrement('stock_quantity', $item->quantity);
        }

        return [$lineData, round($subtotal, 2)];
    }

    private function abortUnless(bool $condition, string $message): void
    {
        abort_unless($condition, 422, $message);
    }

    private function generateOrderReference(): string
    {
        do {
            $ref = 'ORD-' . strtoupper(Str::random(6));
        } while (Order::where('reference', $ref)->exists());

        return $ref;
    }

    private function generatePaymentReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}
