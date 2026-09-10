@extends('layouts.app')

@section('title', $order->reference . ' — ' . config('app.name'))

@php $payment = $order->payments->sortByDesc('id')->first(); @endphp

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8" x-data="{ cancelling: false }">
    <a href="{{ route('consumer.orders.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My orders</a>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $order->reference }}</h1>
            <x-order-status :status="$order->status" />
        </div>
        @if ($order->canBeCancelled())
            <button type="button" @click="cancelling = ! cancelling" class="shrink-0 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">Cancel order</button>
        @endif
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        {{ $order->providerProfile->business_name ?: $order->providerProfile->user->name }} · Placed {{ $order->created_at->diffForHumans() }}
    </p>

    @if ($order->canBeCancelled())
        <form x-show="cancelling" x-cloak x-collapse method="POST" action="{{ route('consumer.orders.cancel', $order) }}" class="mt-3 max-w-sm space-y-2">
            @csrf
            <textarea name="reason" rows="2" maxlength="500" placeholder="Reason (optional)"
                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
            <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 sm:w-auto">Confirm cancellation</button>
        </form>
    @endif

    {{-- Status timeline --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @php
            $steps = ['pending' => 'Placed', 'confirmed' => 'Confirmed', 'ready' => $order->isPickup() ? 'Ready for pickup' : 'Shipped', 'completed' => $order->isPickup() ? 'Collected' : 'Delivered'];
            $order_of = array_flip(array_keys($steps));
            $current = $order->isCancelled() ? -1 : ($order_of[$order->status] ?? 0);
        @endphp
        @if ($order->isCancelled())
            <p class="text-sm font-semibold text-red-600 dark:text-red-400">This order was cancelled{{ $order->cancel_reason ? ': ' . $order->cancel_reason : '.' }}</p>
        @else
            <div class="flex items-center justify-between">
                @foreach ($steps as $key => $label)
                    <div class="flex flex-1 flex-col items-center text-center">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold
                            {{ $loop->index <= $current ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600' }}">
                            {{ $loop->index + 1 }}
                        </span>
                        <span class="mt-1.5 text-[11px] font-medium {{ $loop->index <= $current ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600' }}">{{ $label }}</span>
                    </div>
                    @if (! $loop->last)
                        <div class="h-0.5 flex-1 {{ $loop->index < $current ? 'bg-brand-600' : 'bg-slate-100 dark:bg-slate-800' }}"></div>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($order->isDelivery() && $order->delivery_method)
            <p class="mt-4 rounded-lg bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $order->delivery_method }}</p>
        @endif
        @if ($order->isPickup() && $order->isReady())
            <p class="mt-4 rounded-lg bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                Ready to collect from {{ $order->providerProfile->address ?: 'the provider\'s shop' }}.
            </p>
        @endif
    </div>

    {{-- Items --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Items</h2>
        <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($order->items as $item)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $item->product_name }}</p>
                        <p class="text-xs text-slate-400">Rs. {{ number_format((float) $item->unit_price, 0) }} × {{ $item->quantity }}</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format((float) $item->line_total, 0) }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-4 space-y-1.5 border-t-2 border-slate-100 pt-4 text-sm dark:border-slate-800">
            <div class="flex justify-between text-slate-500 dark:text-slate-400"><span>Subtotal</span><span>Rs. {{ number_format((float) $order->subtotal, 0) }}</span></div>
            @if ((float) $order->discount_amount > 0)
                <div class="flex justify-between text-brand-700 dark:text-brand-400"><span>Coupon{{ $order->coupon ? ' (' . $order->coupon->code . ')' : '' }}</span><span>&minus; Rs. {{ number_format((float) $order->discount_amount, 0) }}</span></div>
            @endif
            <div class="flex justify-between text-slate-500 dark:text-slate-400"><span>Shipping</span><span>Rs. {{ number_format((float) $order->shipping_amount, 0) }}</span></div>
            <div class="flex justify-between font-display text-base font-extrabold text-slate-900 dark:text-white"><span>Total</span><span>Rs. {{ number_format((float) $order->total_amount, 0) }}</span></div>
        </div>
    </div>

    {{-- Rate your purchase --}}
    @if ($order->isCompleted())
        @php $reviewable = $order->reviewableItems(); @endphp
        @if ($reviewable->isNotEmpty() || $order->reviews->isNotEmpty())
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Rate your purchase</h2>
                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($order->reviews as $review)
                        <div class="py-4">
                            <div class="flex items-center justify-between gap-2">
                                @if ($review->product)
                                    <a href="{{ route('shop.show', $review->product) }}" class="text-sm font-semibold text-slate-800 hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">{{ $review->product->name }}</a>
                                @else
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Product</p>
                                @endif
                                <span class="text-sm font-bold text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            @if ($review->comment)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $review->comment }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-400">You rated this on {{ $review->created_at->format('d M Y') }}</p>
                        </div>
                    @endforeach
                    @foreach ($reviewable as $item)
                        <div class="py-4" x-data="{ rating: 0 }">
                            @if ($item->product)
                                <a href="{{ route('shop.show', $item->product) }}" class="text-sm font-semibold text-slate-800 hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">{{ $item->product_name }}</a>
                            @else
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $item->product_name }}</p>
                            @endif
                            <form method="POST" action="{{ route('consumer.orders.reviews.store', $order) }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <input type="hidden" name="rating" :value="rating">
                                <div class="flex items-center gap-1">
                                    <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                        <button type="button" @click="rating = star" class="text-2xl leading-none transition" :class="star <= rating ? 'text-amber-500' : 'text-slate-300 dark:text-slate-600'">★</button>
                                    </template>
                                </div>
                                <textarea name="comment" rows="2" maxlength="1000" placeholder="Optional — tell others what you thought"
                                    class="mt-2 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                                <button type="submit" :disabled="rating === 0" :class="rating === 0 ? 'cursor-not-allowed opacity-50' : ''" class="mt-2 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Submit rating</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Delivery / pickup + payment --}}
    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $order->isPickup() ? 'Pickup' : 'Delivery' }}</h2>
            @if ($order->isDelivery())
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
            @else
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $order->providerProfile->address ?: 'Collect from the provider' }}</p>
                @if ($order->providerProfile->pickup_hours)
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ $order->providerProfile->pickup_hours }}
                    </p>
                @endif
                @if ($order->providerProfile->mapsUrl())
                    <a href="{{ $order->providerProfile->mapsUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2c4.4 0 8 3.6 8 8 0 5-8 12-8 12S4 15 4 10c0-4.4 3.6-8 8-8z" stroke-linejoin="round"/></svg>
                        Get directions on Google Maps &rarr;
                    </a>
                @endif
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Payment</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                {{ $order->payment_method === 'cash' ? ($order->isPickup() ? 'Pay at pickup' : 'Cash on delivery') : 'Bank transfer' }}
            </p>
            @if ($payment)
                <p class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-400">Status: <x-payment-status :status="$payment->status" /></p>
            @endif
        </div>
    </div>
</section>
@endsection
