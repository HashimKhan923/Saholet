@extends('layouts.app')

@section('title', 'My bookings — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My bookings</h1>
        </div>
        <a href="{{ route('services.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New booking</a>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($bookings as $booking)
            <a href="{{ route('consumer.bookings.show', $booking) }}"
               class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->service->name }}</span>
                            <x-booking-status :status="$booking->status" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }}
                            · {{ $booking->dateLabel() }} at {{ $booking->timeLabel() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($booking->price, 0) }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $booking->reference }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No bookings yet</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Browse services and book a verified professional.</p>
                <a href="{{ route('services.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">Browse services</a>
            </div>
        @endforelse
    </div>

    {{ $bookings->links() }}
</section>
@endsection