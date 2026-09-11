@extends('layouts.app')

@section('title', 'Your cart — ' . config('app.name'))
@section('robots', 'noindex, nofollow')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Your cart</h1>

    @if ($groups->isEmpty())
        <div class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
            <svg viewBox="0 0 24 24" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Your cart is empty.</p>
            <a href="{{ route('shops.index') }}" class="mt-5 inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Browse the shop</a>
        </div>
    @else
        @php
            $grandSubtotal = $groups->sum('subtotal');
            $grandDiscount = $groups->sum('discount');
            $grandTotal = $grandSubtotal - $grandDiscount;
        @endphp

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            {{-- ═══ Left: products, one row per item, grouped by shop ═══ --}}
            <div class="space-y-6 lg:col-span-2">
                @foreach ($groups as $group)
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <a href="{{ route('providers.show', $group['provider']) }}" class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900 hover:text-brand-700 dark:border-slate-800 dark:text-white dark:hover:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9M3 9h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $group['provider']->business_name ?: $group['provider']->user->name }}
                        </a>

                        {{-- Column header — the "tabular" cue, hidden on the smallest screens --}}
                        <div class="hidden grid-cols-[1fr_auto_auto_auto] items-center gap-3 px-4 pt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400 sm:grid">
                            <span>Product</span>
                            <span class="w-24 text-center">Qty</span>
                            <span class="w-20 text-end">Total</span>
                            <span class="w-6"></span>
                        </div>

                        <div class="divide-y divide-slate-100 px-4 dark:divide-slate-800">
                            @foreach ($group['items'] as $item)
                                <div class="grid grid-cols-[auto_1fr] items-center gap-3 py-3 sm:grid-cols-[1fr_auto_auto_auto]">
                                    <div class="col-span-2 flex items-center gap-3 sm:col-span-1">
                                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                            @if ($item->product->photos->isNotEmpty())
                                                <img src="{{ $item->product->photos->first()->url() }}" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('shop.show', $item->product) }}" class="line-clamp-2 text-sm font-medium text-slate-800 hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">{{ $item->product->name }}</a>
                                            <p class="text-xs text-slate-400">Rs. {{ number_format((float) $item->product->price, 0) }} each</p>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('consumer.cart.items.update', $item) }}" class="flex w-24 items-center gap-1 justify-self-start sm:justify-self-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock_quantity }}"
                                            onchange="this.form.submit()"
                                            class="w-16 rounded-lg border border-slate-300 px-2 py-1.5 text-center text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    </form>

                                    <span class="w-20 text-end text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format($item->lineTotal(), 0) }}</span>

                                    <form method="POST" action="{{ route('consumer.cart.items.destroy', $item) }}" class="justify-self-end">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30" aria-label="Remove">
                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ═══ Right: summary per shop + grand total ═══ --}}
            <div class="space-y-4 lg:col-span-1">
                @foreach ($groups as $group)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ showCoupon: {{ $group['coupon'] ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $group['provider']->business_name ?: $group['provider']->user->name }}</h2>
                            <span class="text-xs text-slate-400">{{ $group['items']->sum('quantity') }} item(s)</span>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <dt>Subtotal</dt>
                                <dd>Rs. {{ number_format($group['subtotal'], 0) }}</dd>
                            </div>

                            @if ($group['coupon'])
                                <div class="flex justify-between text-brand-700 dark:text-brand-400">
                                    <dt class="flex items-center gap-1.5">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8a2 2 0 0 0-2-2h-4l-6.3 6.3a2 2 0 0 0 0 2.8l3.9 3.9a2 2 0 0 0 2.8 0L20 12Z" stroke-linejoin="round"/><circle cx="15.5" cy="8.5" r="1"/></svg>
                                        {{ $group['coupon']->code }} applied
                                    </dt>
                                    <dd>&minus; Rs. {{ number_format($group['discount'], 0) }}</dd>
                                </div>
                            @endif

                        </dl>

                        {{-- Coupon --}}
                        <div class="mt-4 border-t-2 border-slate-100 pt-4 dark:border-slate-800">
                            @if ($group['coupon'])
                                <form method="POST" action="{{ route('consumer.cart.coupon.remove', $group['provider']) }}" class="flex items-center justify-between">
                                    @csrf
                                    @method('DELETE')
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Coupon <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $group['coupon']->code }}</span> ({{ $group['coupon']->label() }})</span>
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400">Remove</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('consumer.cart.coupon.apply') }}" class="flex gap-2">
                                    @csrf
                                    <input type="hidden" name="provider_profile_id" value="{{ $group['provider']->id }}">
                                    <input type="text" name="code" placeholder="Coupon code" maxlength="32"
                                        class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    <button type="submit" class="shrink-0 rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Apply</button>
                                </form>
                            @endif
                        </div>

                        <div class="mt-4 flex justify-between border-t-2 border-slate-100 pt-4 font-display text-base font-extrabold text-slate-900 dark:border-slate-800 dark:text-white">
                            <span>Total</span>
                            <span>Rs. {{ number_format($group['subtotal'] - $group['discount'], 0) }}</span>
                        </div>
                    </div>
                @endforeach

                {{-- Grand total across shops + checkout CTA --}}
                <div class="rounded-2xl border-2 border-brand-200 bg-brand-50/60 p-5 dark:border-brand-900 dark:bg-brand-950/30">
                    @if ($groups->count() > 1)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $groups->count() }} orders will be placed — one per shop.</p>
                    @endif
                    <div class="mt-1 flex items-center justify-between">
                        <span class="font-display text-lg font-extrabold text-slate-900 dark:text-white">Grand total</span>
                        <span class="font-display text-xl font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format($grandTotal, 0) }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Shipping isn't included yet — it's calculated on the next page once you pick an address.</p>
                    <a href="{{ route('consumer.checkout.show') }}" class="btn-shine mt-4 block w-full rounded-xl bg-brand-600 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Proceed to checkout</a>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
