@extends('layouts.provider')

@section('title', 'Shipping & delivery — ' . config('app.name'))
@section('page_title', 'Shipping & delivery')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Shipping & delivery</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">How you price shipping — pick one, or leave delivery off if you only offer pickup.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.shop-settings.shipping.update') }}" class="mt-6 space-y-5"
          x-data="{ type: '{{ old('shipping_type', $profile->shipping_type ?? '') }}', submitting: false }" @submit="submitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-3">
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

        <button type="submit" :disabled="submitting" class="btn-shine inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">Save shipping settings</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
    </form>
</section>
@endsection
