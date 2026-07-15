@extends('layouts.app')

@section('title', $subscription->plan->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.subscriptions.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My subscriptions</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $subscription->plan->name }}</h1>
        <x-subscription-status :status="$subscription->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subscription->reference }}</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    @if ($subscription->isPendingAssignment())
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-400">
            We're assigning a provider for your subscription — you'll be notified as soon as your first visit is scheduled.
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Next visit</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->next_visit_date->format('D, d M Y') }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Visits used</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->visits_used }}{{ $subscription->plan->total_visits ? ' / ' . $subscription->plan->total_visits : ' (ongoing)' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Price per visit</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format($subscription->plan->price_per_visit, 0) }}</dd></div>
            @if ($subscription->providerProfile)
                <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->providerProfile->business_name ?: $subscription->providerProfile->user->name }}</dd></div>
            @endif
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Service address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->address }}</dd></div>
        </dl>
    </div>

    @if ($subscription->bookings->isNotEmpty())
        <div class="mt-8">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Visit history</h2>
            <div class="mt-4 space-y-3">
                @foreach ($subscription->bookings as $booking)
                    <a href="{{ route('consumer.bookings.show', $booking) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->scheduled_date->format('d M Y') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->reference }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($booking->isPayable())
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Payment due</span>
                            @endif
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($subscription->isCancellable())
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Cancel this subscription</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Any already-scheduled visits will not be affected — only future visits stop being generated.</p>
            <x-confirm-form :action="route('consumer.subscriptions.cancel', $subscription)" method="POST"
                button-label="Cancel subscription" button-class="mt-3 rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                title="Cancel this subscription?" confirm-label="Cancel subscription" />
        </div>
    @endif
</section>
@endsection
