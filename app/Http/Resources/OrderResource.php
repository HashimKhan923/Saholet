<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'fulfillment_method' => $this->fulfillment_method,
            'payment_method' => $this->payment_method,
            'provider' => new ProviderProfileResource($this->whenLoaded('providerProfile')),
            'consumer' => $this->when(
                $this->relationLoaded('consumer'),
                fn () => ['id' => $this->consumer?->id, 'name' => $this->consumer?->name, 'phone' => $this->consumer?->phone]
            ),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_lat' => $this->shipping_lat !== null ? (float) $this->shipping_lat : null,
            'shipping_lng' => $this->shipping_lng !== null ? (float) $this->shipping_lng : null,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'coupon_code' => $this->whenLoaded('coupon', fn () => $this->coupon?->code),
            'shipping_amount' => (float) $this->shipping_amount,
            'total_amount' => (float) $this->total_amount,
            'delivery_method' => $this->delivery_method,
            'tracking_reference' => $this->tracking_reference,
            'cancel_reason' => $this->cancel_reason,
            'ready_at' => $this->ready_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'permissions' => $user ? [
                'can_cancel' => $this->canBeCancelled(),
                'is_provider' => $this->isProviderUser($user),
            ] : null,
        ];
    }
}
