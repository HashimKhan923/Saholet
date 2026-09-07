@extends('layouts.provider')

@section('title', $order->reference . ' — ' . config('app.name'))
@section('page_title', 'Order detail')

@php $payment = $order->payments->sortByDesc('id')->first(); @endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8" x-data="{ cancelling: false }">

    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('provider.orders.index') }}" />
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $order->reference }}</h1>
                <x-order-status :status="$order->status" />
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    {{ $order->isPickup() ? 'Pickup' : 'Delivery' }}
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Placed {{ $order->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- ═══ Left column ═══ --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Items --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Subtotal</span><span>Rs. {{ number_format((float) $order->subtotal, 0) }}</span>
                    </div>
                    @if ((float) $order->discount_amount > 0)
                    <div class="flex justify-between text-brand-700 dark:text-brand-400">
                        <span>Coupon{{ $order->coupon ? ' (' . $order->coupon->code . ')' : '' }}</span><span>&minus; Rs. {{ number_format((float) $order->discount_amount, 0) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Shipping</span><span>Rs. {{ number_format((float) $order->shipping_amount, 0) }}</span>
                    </div>
                    <div class="flex justify-between font-display text-base font-extrabold text-slate-900 dark:text-white">
                        <span>Total</span><span>Rs. {{ number_format((float) $order->total_amount, 0) }}</span>
                    </div>
                </div>
            </section>

            {{-- Customer & fulfillment --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $order->isPickup() ? 'Pickup' : 'Delivery' }} details</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Customer</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $order->consumer->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $order->consumer->phone ?: '—' }}</dd>
                    </div>
                    @if ($order->isDelivery())
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Delivery address</dt>
                            <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $order->shipping_address }}, {{ $order->shipping_city }}</dd>
                        </div>
                        @if ($order->delivery_method)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tracking information</dt>
                                <dd class="mt-1 whitespace-pre-line font-medium text-slate-800 dark:text-slate-200">{{ $order->delivery_method }}</dd>
                            </div>
                        @endif
                    @endif
                </dl>
            </section>

            {{-- Timeline --}}
            @if ($order->events->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Timeline</h2>
                    <ol class="mt-4">
                        @foreach ($order->events as $event)
                            <x-timeline-item :last="$loop->last">
                                <span class="text-sm text-slate-600 dark:text-slate-300">
                                    @if ($event->to_status)
                                        Status changed to <span class="font-semibold">{{ ucfirst($event->to_status) }}</span>
                                    @else
                                        {{ $event->note }}
                                    @endif
                                    <span class="ms-1 text-xs text-slate-400">· {{ $event->created_at->diffForHumans() }}</span>
                                </span>
                            </x-timeline-item>
                        @endforeach
                    </ol>
                </section>
            @endif
        </div>

        {{-- ═══ Right column ═══ --}}
        <div class="space-y-6">
            {{-- Payment --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Payment</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ $order->payment_method === 'cash' ? ($order->isPickup() ? 'Pay at pickup (cash)' : 'Cash on delivery') : 'Bank transfer' }}
                </p>
                @if ($payment)
                    <p class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-400">Status: <x-payment-status :status="$payment->status" /></p>
                @endif
            </section>

            {{-- Actions --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Actions</h2>

                @if ($order->isPending())
                    <form method="POST" action="{{ route('provider.orders.confirm', $order) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Confirm order</button>
                    </form>
                @elseif ($order->isConfirmed())
                    <form method="POST" action="{{ route('provider.orders.ready', $order) }}" class="mt-4 space-y-3">
                        @csrf
                        @if ($order->isDelivery())
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Tracking information <span class="text-slate-400">(optional)</span></label>
                                <textarea name="delivery_method" rows="3" maxlength="1000" placeholder="You can type anything here — a rider service (Bykea, InDrive, Yango), who's delivering it, when to expect it…"
                                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('delivery_method') }}</textarea>
                                <x-field-error name="delivery_method" />
                            </div>
                        @endif
                        <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            {{ $order->isPickup() ? 'Mark ready for pickup' : 'Mark as dispatched' }}
                        </button>
                    </form>
                @elseif ($order->isReady())
                    <form method="POST" action="{{ route('provider.orders.complete', $order) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            {{ $order->isPickup() ? 'Mark collected & paid' : 'Mark delivered' }}
                        </button>
                        @if ($order->payment_method === 'cash')
                            <p class="mt-2 text-xs text-slate-400">This records the cash payment and deducts your commission.</p>
                        @endif
                    </form>
                @endif

                @if ($order->canBeCancelled())
                    <button type="button" @click="cancelling = ! cancelling" class="mt-3 w-full rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">
                        Cancel order
                    </button>
                    <form x-show="cancelling" x-cloak x-collapse method="POST" action="{{ route('provider.orders.cancel', $order) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="reason" rows="2" maxlength="500" placeholder="Reason (optional)"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                        <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Confirm cancellation</button>
                    </form>
                @endif

                @if ($order->isCompleted() || $order->isCancelled())
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No further action needed.</p>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
