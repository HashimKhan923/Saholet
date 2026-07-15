@extends('layouts.provider')

@section('title', 'Bookings — ' . config('app.name'))
@section('page_title', 'Bookings')

@php
    $tabs = [
        'all'         => 'All',
        'pending'     => 'Pending',
        'confirmed'   => 'Confirmed',
        'in_progress' => 'In progress',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
    ];

    $emptyCopy = [
        'all'         => ['No bookings yet', 'Once customers book your services, they’ll appear here.'],
        'pending'     => ['Nothing awaiting你 action', 'You’re all caught up — no bookings need a decision.'],
        'confirmed'   => ['No confirmed bookings', 'Confirmed jobs waiting to start will show up here.'],
        'in_progress' => ['Nothing in progress', 'Start a confirmed booking to see it here.'],
        'completed'   => ['No completed jobs yet', 'Finished jobs and their payouts will be listed here.'],
        'cancelled'   => ['No cancelled bookings', 'Nothing has been cancelled or declined.'],
    ];
    [$emptyTitle, $emptyBody] = $emptyCopy[$filter] ?? $emptyCopy['all'];
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Dashboard
            </a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Bookings</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $counts['all'] }} total ·
                <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $counts['pending'] }} awaiting your decision</span>
            </p>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('provider.bookings.index') }}" class="relative w-full sm:w-72">
            <input type="hidden" name="status" value="{{ $filter }}">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
            </span>
            <input type="search" name="q" value="{{ $search }}" placeholder="Reference, customer, service…"
                class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pe-10 ps-10 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-brand-950">
            @if ($search !== '')
                <a href="{{ route('provider.bookings.index', ['status' => $filter]) }}"
                   class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 transition hover:text-slate-600" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                </a>
            @endif
        </form>
    </div>

    {{-- ═══ Status tabs ═══ --}}
    <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <nav class="flex min-w-max gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900" aria-label="Filter bookings by status">
            @foreach ($tabs as $key => $label)
                @php $active = $filter === $key; @endphp
                <a href="{{ route('provider.bookings.index', array_filter(['status' => $key, 'q' => $search])) }}"
                   @if ($active) aria-current="page" @endif
                   class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition
                       {{ $active
                           ? 'bg-brand-600 text-white shadow-sm'
                           : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                    {{ $label }}
                    @if (($counts[$key] ?? 0) > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold
                            {{ $active
                                ? 'bg-white/25 text-white'
                                : ($key === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}">
                            {{ $counts[$key] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ═══ List ═══ --}}
    <div class="space-y-3">
        @forelse ($bookings as $booking)
            @php $needsAction = $booking->isPending(); @endphp
            <a href="{{ route('provider.bookings.show', $booking) }}"
               class="card-lift group relative block overflow-hidden rounded-2xl border bg-white p-5 shadow-sm dark:bg-slate-900
                   {{ $needsAction
                       ? 'border-amber-200 dark:border-amber-900/60'
                       : 'border-slate-200 hover:border-brand-200 dark:border-slate-800 dark:hover:border-brand-800' }}">

                @if ($needsAction)
                    <span aria-hidden="true" class="absolute inset-y-0 start-0 w-1 bg-amber-400"></span>
                @endif

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        {{-- Date chip --}}
                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $booking->scheduled_date->format('M') }}</span>
                            <span class="font-display text-lg font-extrabold leading-none text-slate-900 dark:text-white">{{ $booking->scheduled_date->format('d') }}</span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $booking->service?->name ?? 'Service' }}</span>
                                <x-booking-status :status="$booking->status" />
                            </div>
                            <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ $booking->consumer?->name }} · {{ $booking->timeLabel() }} · ~{{ $booking->duration_minutes }} min
                            </p>
                            <p class="mt-1 truncate text-xs text-slate-400">{{ $booking->address }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center justify-between gap-4 sm:justify-end">
                        <div class="text-start sm:text-end">
                            <p class="font-display text-base font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $booking->price, 0) }}</p>
                            <p class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $booking->reference }}</p>
                        </div>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600 rtl:rotate-180 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
                </span>
                <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">
                    {{ $search !== '' ? 'No matches found' : $emptyTitle }}
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ $search !== '' ? 'Try a different reference, customer or service name.' : $emptyBody }}
                </p>
                @if ($search !== '' || $filter !== 'all')
                    <a href="{{ route('provider.bookings.index') }}" class="mt-5 inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Clear filters
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div>{{ $bookings->links() }}</div>
    @endif
</div>
@endsection