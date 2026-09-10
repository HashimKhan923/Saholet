<?php

namespace App\Services;

use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Drives an Order through pending → confirmed → ready → completed (or →
 * cancelled), the provider-facing side of fulfillment. Payment settlement —
 * cash commission on completion, or releasing an escrowed bank-transfer
 * payment — happens here too, since both only ever fire at these two exact
 * transitions (complete, cancel).
 */
class OrderFulfillmentService
{
    public function __construct(
        private WalletService $wallets,
        private Notifier $notifier,
    ) {}

    public function confirm(Order $order): void
    {
        $this->transition($order, Order::STATUS_CONFIRMED);

        $this->notifier->notify(
            $order->consumer,
            'order',
            'Order confirmed',
            'Your order ' . $order->reference . ' has been confirmed by the shop.',
            route('consumer.orders.show', $order),
        );
    }

    /**
     * $trackingInfo is free text, entirely optional — most local delivery
     * here is a rider (Bykea/InDrive/Yango) or the provider themselves, not
     * a courier with a trackable waybill, so there's nothing to validate the
     * shape of. A provider can leave it blank, name a rider service, or note
     * anything else worth telling the customer.
     */
    public function markReady(Order $order, ?string $trackingInfo): void
    {
        $order->update([
            'delivery_method' => $order->isDelivery() ? $trackingInfo : null,
        ]);

        $this->transition($order, Order::STATUS_READY);

        $message = $order->isPickup()
            ? 'Your order ' . $order->reference . ' is ready for pickup.'
            : 'Your order ' . $order->reference . ' is on its way.';

        $this->notifier->notify($order->consumer, 'order', 'Order ready', $message, route('consumer.orders.show', $order));
    }

    /**
     * Delivered, or collected at pickup — and, in the same step, wherever
     * this order's money actually settles. For a cash order the provider is
     * physically present at that exact moment (handing over goods, taking
     * payment), so their completion action also records the cash payment
     * and charges commission — one action, not a separate consumer
     * confirmation round-trip. A bank-transfer order already has its
     * Payment from checkout; completing here just releases its escrow.
     */
    public function complete(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $this->transition($order, Order::STATUS_COMPLETED);

            if ($order->payment_method === Payment::GATEWAY_CASH) {
                $payment = Payment::create([
                    'reference' => $this->generatePaymentReference(),
                    'order_id' => $order->id,
                    'consumer_id' => $order->consumer_id,
                    'gateway' => Payment::GATEWAY_CASH,
                    'amount' => $order->total_amount,
                    'status' => Payment::STATUS_PENDING,
                ]);

                $this->wallets->chargeCashCommission($payment, $order->providerProfile->user);

                return;
            }

            $payment = $order->payments()->where('status', Payment::STATUS_ESCROW)->latest()->first();

            if ($payment) {
                $this->wallets->release($payment, $order->providerProfile->user);
            }
        });
    }

    public function cancel(Order $order, ?string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
                }
            }

            $payment = $order->payments()->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_ESCROW])->latest()->first();

            if ($payment?->isEscrow()) {
                $this->wallets->refund($payment, $order->providerProfile->user);
            } elseif ($payment) {
                $payment->update(['status' => Payment::STATUS_REFUNDED, 'refunded_at' => now()]);
            }

            // The customer never actually got the discount's benefit — free their one-time redemption back up.
            if ($order->coupon_id) {
                CouponRedemption::where('coupon_id', $order->coupon_id)->where('order_id', $order->id)->delete();
            }

            $order->update(['cancel_reason' => $reason]);
            $this->transition($order, Order::STATUS_CANCELLED);
        });

        $this->notifier->notify(
            $order->consumer,
            'order',
            'Order cancelled',
            'Your order ' . $order->reference . ' was cancelled.' . ($reason ? ' Reason: ' . $reason : ''),
            route('consumer.orders.show', $order),
        );
    }

    private function transition(Order $order, string $to): void
    {
        $from = $order->status;

        $order->update([
            'status' => $to,
            'ready_at' => $to === Order::STATUS_READY ? now() : $order->ready_at,
            'completed_at' => $to === Order::STATUS_COMPLETED ? now() : $order->completed_at,
            'cancelled_at' => $to === Order::STATUS_CANCELLED ? now() : $order->cancelled_at,
        ]);

        $order->events()->create([
            'type' => OrderEvent::TYPE_STATUS_CHANGED,
            'from_status' => $from,
            'to_status' => $to,
        ]);
    }

    private function generatePaymentReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}
