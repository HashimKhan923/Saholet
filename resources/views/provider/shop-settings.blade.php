@extends('layouts.provider')

@section('title', 'Shop settings — ' . config('app.name'))
@section('page_title', 'Shop settings')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Shop settings</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Configure how customers get products from your shop — delivery, self-pickup, or both. You need at least one before you can add products.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.shop-settings.update') }}" class="mt-6 space-y-5"
          x-data="{
              type: '{{ old('shipping_type', $profile->shipping_type ?? '') }}',
              pickup: {{ old('pickup_enabled', $profile->pickup_enabled) ? 'true' : 'false' }},
              submitting: false,
          }" @submit="submitting = true">
        @csrf

        {{-- Delivery --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Delivery</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">How you price shipping — pick one, or leave delivery off if you only offer pickup.</p>

            <div class="mt-4 space-y-3">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                       :class="type === '' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="shipping_type" value="" x-model="type" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">No delivery</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Customers can only pick up from your shop.</span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                       :class="type === 'flat' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="shipping_type" value="flat" x-model="type" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span class="flex-1">
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">Flat rate</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Same shipping fee on every order, anywhere.</span>
                        <span x-show="type === 'flat'" x-cloak class="mt-3 block">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Rate (Rs.)</label>
                            <input type="number" name="shipping_flat_rate" min="0" step="0.01" value="{{ old('shipping_flat_rate', $profile->shipping_flat_rate) }}"
                                class="mt-1 block w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <x-field-error name="shipping_flat_rate" />
                        </span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                       :class="type === 'percentage' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="shipping_type" value="percentage" x-model="type" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span class="flex-1">
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">Percentage of order</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Shipping scales with what's in the cart.</span>
                        <span x-show="type === 'percentage'" x-cloak class="mt-3 block">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Percentage (%)</label>
                            <input type="number" name="shipping_percentage" min="0" max="100" step="0.01" value="{{ old('shipping_percentage', $profile->shipping_percentage) }}"
                                class="mt-1 block w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <x-field-error name="shipping_percentage" />
                        </span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                       :class="type === 'free' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="shipping_type" value="free" x-model="type" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">Free delivery</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">No shipping charge — the cost is on you.</span>
                    </span>
                </label>
            </div>

            <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                Delivery is offered everywhere, at whatever rate you pick above — we don't check the customer's exact address against a coverage map. If an order comes in from somewhere you can't actually reach, just call the customer or cancel the order.
            </p>
        </div>

        {{-- Pickup --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Self-pickup</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Let customers order and collect from your shop address
                        @if ($profile->address)
                            (<span class="font-medium">{{ $profile->address }}</span> — from your profile).
                        @else
                            — set your shop address on your <a href="{{ route('provider.onboarding') }}" class="text-brand-700 underline dark:text-brand-400">provider profile</a> first.
                        @endif
                    </p>
                </div>
                <input type="hidden" name="pickup_enabled" :value="pickup ? 1 : 0">
                <button type="button" role="switch" :aria-checked="pickup" @click="pickup = ! pickup"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent shadow-inner transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    :class="pickup ? 'bg-gradient-to-r from-brand-500 to-brand-600' : 'bg-slate-200 dark:bg-slate-700'"
                    aria-label="Toggle self-pickup">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                        :class="pickup ? 'translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>

            <div x-show="pickup" x-cloak class="mt-4">
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Shop hours <span class="text-slate-400">(shown to customers picking up, e.g. "Mon–Sat 9am–8pm")</span></label>
                <input type="text" name="pickup_hours" maxlength="255" value="{{ old('pickup_hours', $profile->pickup_hours) }}"
                    placeholder="Mon–Sat 9am–8pm"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <x-field-error name="pickup_hours" />
            </div>
        </div>

        <button type="submit" :disabled="submitting" class="btn-shine inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">Save shop settings</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
    </form>

</section>
@endsection
