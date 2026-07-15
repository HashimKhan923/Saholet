@extends('layouts.app')

@section('title', $booking->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.bookings.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; My bookings</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $booking->service->name }}</h1>
        <x-booking-status :status="$booking->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Reference {{ $booking->reference }}</p>

    @if (! $booking->isCancelled())
        <a href="{{ route('bookings.room', $booking) }}"
           class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-5A8 8 0 1 1 21 12z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Messages &amp; live tracking
        </a>
    @endif

    @if ($booking->isCancelled() && $booking->cancellation_reason)
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <span class="font-semibold">Cancelled:</span> {{ $booking->cancellation_reason }}
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->service->category->name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Date</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->dateLabel() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Time</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->timeLabel() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Duration</dt><dd class="font-medium text-slate-800 dark:text-slate-200">~ {{ $booking->duration_minutes }} min</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Price</dt><dd class="font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($booking->price, 0) }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Service address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->address }}</dd></div>
            @if ($booking->notes)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->notes }}</dd></div>
            @endif
        </dl>
    </div>

    @if ($booking->canBeCancelledByConsumer())
        <div class="mt-6">
            <x-confirm-form :action="route('consumer.bookings.cancel', $booking)"
                button-label="Cancel booking" button-class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                title="Cancel this booking?" confirm-label="Cancel booking" />
        </div>
    @endif
</section>
@endsection