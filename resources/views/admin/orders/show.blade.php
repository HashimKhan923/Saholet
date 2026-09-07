@extends('layouts.admin')

@section('title', $order->reference . ' — ' . config('app.name'))

@php $payment = $order->payments->sortByDesc('id')->first(); @endphp

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Shop orders</a>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $order->reference }}</h1>
        <x-order-status :status="$order->status" />
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $order->isPickup() ? 'Pickup' : 'Delivery' }}</span>
    </div>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Customer</h2>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">{{ $order->consumer->name }}</p>
            <p class="text-xs text-slate-400">{{ $order->consumer->phone }} · {{ $order->consumer->email }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Shop</h2>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                <a href="{{ route('admin.providers.show', $order->providerProfile) }}" class="hover:text-brand-700 dark:hover:text-brand-400">
                    {{ $order->providerProfile->business_name ?: $order->providerProfile->user->name }}
                </a>
            </p>
        </div>
    </div>

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

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $order->isPickup() ? 'Pickup' : 'Delivery' }}</h2>
            @if ($order->isDelivery())
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                @if ($order->delivery_method)
                    <p class="mt-1 text-xs text-slate-400">{{ $order->delivery_method }}</p>
                @endif
            @else
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $order->providerProfile->address ?: '—' }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Payment</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $order->payment_method === 'cash' ? 'Cash' : 'Bank transfer' }}</p>
            @if ($payment)
                <p class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-400">Status: <x-payment-status :status="$payment->status" /></p>
                @if ($payment->isBankTransfer() && $payment->isPending())
                    <a href="{{ route('admin.payments.show', $payment) }}" class="mt-2 inline-flex text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">Verify this payment &rarr;</a>
                @endif
            @endif
        </div>
    </div>

    @if ($order->events->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Timeline</h2>
            <ol class="mt-4">
                @foreach ($order->events as $event)
                    <x-timeline-item :last="$loop->last">
                        <span class="text-sm text-slate-600 dark:text-slate-300">
                            {{ $event->to_status ? 'Status changed to ' . ucfirst($event->to_status) : $event->note }}
                            <span class="ms-1 text-xs text-slate-400">· {{ $event->created_at->diffForHumans() }}</span>
                        </span>
                    </x-timeline-item>
                @endforeach
            </ol>
        </div>
    @endif
</section>
@endsection
