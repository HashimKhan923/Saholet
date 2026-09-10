@extends('layouts.app')

@section('title', 'Checkout — ' . config('app.name'))
@section('robots', 'noindex, nofollow')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Checkout</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.checkout.store') }}" enctype="multipart/form-data" class="mt-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- ═══ Left: customer info, then products ═══ --}}
            <div class="space-y-6">
                {{-- Customer information --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Customer information</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Name</dt>
                            <dd class="font-medium text-slate-800 dark:text-slate-200">{{ auth()->user()->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Phone</dt>
                            <dd class="font-medium text-slate-800 dark:text-slate-200">{{ auth()->user()->phone ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                            <dd class="truncate font-medium text-slate-800 dark:text-slate-200">{{ auth()->user()->email }}</dd>
                        </div>
                    </dl>
                    <a href="{{ route('profile.edit') }}" target="_blank" class="mt-3 inline-block text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">Edit your details &rarr;</a>
                </div>

                {{-- Products in cart, grouped by shop --}}
                @foreach ($groups as $providerId => $items)
                    @php $provider = $items->first()->product->providerProfile; @endphp
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900 dark:border-slate-800 dark:text-white">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9M3 9h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $provider->business_name ?: $provider->user->name }}
                        </p>
                        <div class="divide-y divide-slate-100 px-4 dark:divide-slate-800">
                            @foreach ($items as $item)
                                <div class="flex items-center gap-3 py-3">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                        @if ($item->product->photos->isNotEmpty())
                                            <img src="{{ $item->product->photos->first()->url() }}" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ $item->product->name }}</p>
                                        <p class="text-xs text-slate-400">Qty {{ $item->quantity }} &times; Rs. {{ number_format((float) $item->product->price, 0) }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format($item->lineTotal(), 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ═══ Right: fulfillment + payment + summary per shop — visually distinct from the cart page via the accent header on each card ═══ --}}
            <div class="space-y-6">
                @foreach ($groups as $providerId => $items)
                    @php
                        $provider = $items->first()->product->providerProfile;
                        $subtotal = round($items->sum(fn ($item) => $item->lineTotal()), 2);
                        $appliedCoupon = $appliedCoupons[$providerId] ?? null;
                        $discount = $appliedCoupon ? $appliedCoupon->discountFor($subtotal) : 0.0;
                        $defaultFulfillment = $provider->offersDelivery() ? 'delivery' : 'pickup';
                        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
                    @endphp

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                         x-data="{
                             fulfillment: '{{ old('orders.' . $loop->index . '.fulfillment_method', $defaultFulfillment) }}',
                             payment: '{{ old('orders.' . $loop->index . '.payment_method', 'cash') }}',
                             addressId: {{ old('orders.' . $loop->index . '.address_id', $defaultAddress?->id) ?? 'null' }},
                             subtotal: {{ $subtotal }},
                             discount: {{ $discount }},
                             shippingType: {{ \Illuminate\Support\Js::from($provider->shipping_type) }},
                             flatRate: {{ $provider->shipping_flat_rate ?? 0 }},
                             percentRate: {{ $provider->shipping_percentage ?? 0 }},
                             get netSubtotal() { return this.subtotal - this.discount; },
                             get shippingEstimate() {
                                 if (this.fulfillment !== 'delivery') return 0;
                                 if (this.shippingType === 'flat') return this.flatRate;
                                 if (this.shippingType === 'percentage') return Math.round(this.netSubtotal * this.percentRate) / 100;
                                 if (this.shippingType === 'free') return 0;
                                 return null;
                             },
                             get total() { return this.shippingEstimate === null ? null : this.netSubtotal + this.shippingEstimate; },
                         }">
                        {{-- Accent header — the visual cue this is checkout, not the cart --}}
                        <div class="flex items-center justify-between bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-3">
                            <h2 class="text-sm font-bold text-white">{{ $provider->business_name ?: $provider->user->name }}</h2>
                            <span class="text-xs font-medium text-brand-100">Step {{ $loop->index + 1 }} of {{ $groups->count() }}</span>
                        </div>

                        <input type="hidden" name="orders[{{ $loop->index }}][provider_profile_id]" value="{{ $providerId }}">

                        <div class="p-5">
                            {{-- Fulfillment method --}}
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">How will you get this?</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @if ($provider->offersDelivery())
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 transition"
                                           :class="fulfillment === 'delivery' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                                        <input type="radio" name="orders[{{ $loop->index }}][fulfillment_method]" value="delivery" x-model="fulfillment" class="h-4 w-4 text-brand-600 focus:ring-brand-200">
                                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Delivery</span>
                                    </label>
                                @endif
                                @if ($provider->pickup_enabled)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 transition"
                                           :class="fulfillment === 'pickup' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                                        <input type="radio" name="orders[{{ $loop->index }}][fulfillment_method]" value="pickup" x-model="fulfillment" class="h-4 w-4 text-brand-600 focus:ring-brand-200">
                                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Self-pickup</span>
                                    </label>
                                @endif
                            </div>

                            {{-- Address (delivery only) --}}
                            <div x-show="fulfillment === 'delivery'" x-cloak class="mt-4">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Delivery address</p>
                                @if ($addresses->isEmpty())
                                    <div class="mt-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center text-sm dark:border-slate-700 dark:bg-slate-800">
                                        <a href="{{ route('consumer.addresses.index') }}" target="_blank" class="font-semibold text-brand-700 dark:text-brand-400">Add an address</a> to enable delivery, then come back and refresh.
                                    </div>
                                @else
                                    <div class="mt-2 space-y-2">
                                        @foreach ($addresses as $address)
                                            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 transition"
                                                   :class="addressId === {{ $address->id }} ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                                                <input type="radio" name="orders[{{ $loop->parent->index }}][address_id]" value="{{ $address->id }}" @checked($address->is_default)
                                                    @click="addressId = {{ $address->id }}"
                                                    class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                                                <span class="text-sm">
                                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $address->label }}</span>
                                                    <span class="block text-slate-500 dark:text-slate-400">{{ $address->address }}, {{ $address->city }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Pickup info --}}
                            <div x-show="fulfillment === 'pickup'" x-cloak class="mt-4 rounded-xl bg-slate-50 p-4 text-sm dark:bg-slate-800">
                                @if ($provider->address)
                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->address }}</p>
                                @endif
                                @if ($provider->pickup_hours)
                                    <p class="mt-1 flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        {{ $provider->pickup_hours }}
                                    </p>
                                @endif
                                @if ($provider->mapsUrl())
                                    <a href="{{ $provider->mapsUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2c4.4 0 8 3.6 8 8 0 5-8 12-8 12S4 15 4 10c0-4.4 3.6-8 8-8z" stroke-linejoin="round"/></svg>
                                        Get directions on Google Maps &rarr;
                                    </a>
                                @endif
                            </div>

                            {{-- Payment method --}}
                            <p class="mt-5 text-sm font-semibold text-slate-900 dark:text-white">Payment</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 transition"
                                       :class="payment === 'cash' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                                    <input type="radio" name="orders[{{ $loop->index }}][payment_method]" value="cash" x-model="payment" class="h-4 w-4 text-brand-600 focus:ring-brand-200">
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200" x-text="fulfillment === 'pickup' ? 'Pay at pickup' : 'Cash on delivery'"></span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 transition"
                                       :class="payment === 'bank_transfer' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                                    <input type="radio" name="orders[{{ $loop->index }}][payment_method]" value="bank_transfer" x-model="payment" class="h-4 w-4 text-brand-600 focus:ring-brand-200">
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Bank transfer (pay now)</span>
                                </label>
                            </div>

                            <div x-show="payment === 'bank_transfer'" x-cloak class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800">
                                <dl class="space-y-1.5 text-sm">
                                    @if (!empty($companyAccount['account_number']))
                                        <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Bank</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['bank_name'] }}</dd></div>
                                        <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Account title</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['account_title'] }}</dd></div>
                                        <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Account number</dt><dd class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['account_number'] }}</dd></div>
                                    @endif
                                </dl>
                                <div class="mt-3">
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Upload payment screenshot</label>
                                    <input type="file" name="orders[{{ $loop->index }}][screenshot]" accept="image/*" class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white dark:text-slate-300">
                                    <x-field-error name="orders.{{ $loop->index }}.screenshot" />
                                </div>
                            </div>

                            {{-- Order summary — same shape as the cart page's, plus the live shipping estimate reacting to the choice above --}}
                            <div class="mt-5 border-t-2 border-slate-100 pt-4 dark:border-slate-800">
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                        <dt>Subtotal</dt>
                                        <dd>Rs. {{ number_format($subtotal, 0) }}</dd>
                                    </div>

                                    {{-- Coupon --}}
                                    @if ($appliedCoupon)
                                        <div class="flex justify-between text-brand-700 dark:text-brand-400">
                                            <dt class="flex items-center gap-1.5">
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8a2 2 0 0 0-2-2h-4l-6.3 6.3a2 2 0 0 0 0 2.8l3.9 3.9a2 2 0 0 0 2.8 0L20 12Z" stroke-linejoin="round"/></svg>
                                                {{ $appliedCoupon->code }} applied
                                            </dt>
                                            <dd>&minus; Rs. {{ number_format($discount, 0) }}</dd>
                                        </div>
                                    @endif

                                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                        <dt>Shipping</dt>
                                        <dd>
                                            <span x-show="fulfillment === 'pickup'">Free (pickup)</span>
                                            <span x-show="fulfillment === 'delivery' && shippingEstimate !== null" x-text="'Rs. ' + Number(shippingEstimate).toLocaleString()"></span>
                                            <span x-show="fulfillment === 'delivery' && shippingEstimate === null" class="text-red-600 dark:text-red-400">Delivery pricing not set up</span>
                                        </dd>
                                    </div>
                                </dl>

                                <div class="mt-4 flex justify-between border-t-2 border-slate-100 pt-4 font-display text-base font-extrabold text-slate-900 dark:border-slate-800 dark:text-white">
                                    <span>Order total</span>
                                    <span x-text="total === null ? '—' : 'Rs. ' + Number(total).toLocaleString()"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <button type="submit" :disabled="submitting" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-show="!submitting">{{ $groups->count() > 1 ? 'Place ' . $groups->count() . ' orders' : 'Place order' }}</span>
                    <span x-show="submitting" x-cloak>Placing order…</span>
                </button>
            </div>
        </div>
    </form>
</section>
@endsection
