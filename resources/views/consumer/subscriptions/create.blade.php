@extends('layouts.app')

@section('title', 'Subscribe — ' . $plan->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('subscription-plans.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Maintenance plans</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $plan->name }}</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $plan->description ?? $plan->service->name }} — Rs. {{ number_format($plan->price_per_visit, 0) }} per visit, {{ $plan->frequencyLabel() }}.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.subscriptions.store', $plan) }}" class="mt-8 space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-5">
                <x-address-input :value="old('address')" />
                <x-city-input :value="old('city')" :cities="$cities" />

                <div>
                    <label for="start_date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">First visit date</label>
                    <input id="start_date" name="start_date" type="date" required min="{{ now()->toDateString() }}" value="{{ old('start_date', now()->addDay()->toDateString()) }}"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">We'll assign a provider and confirm the exact time before this date.</p>
                    <x-field-error name="start_date" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="submitting"
                class="inline-flex items-center rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Subscribe</span>
                <span x-show="submitting" x-cloak>Submitting…</span>
            </button>
            <a href="{{ route('subscription-plans.index') }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        </div>
    </form>
</section>
@endsection
