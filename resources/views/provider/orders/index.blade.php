@extends('layouts.provider')

@section('title', 'Orders — ' . config('app.name'))
@section('page_title', 'Orders')

@php
    $tabs = [
        'all' => 'All',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $emptyCopy = [
        'all' => ['No orders yet', 'Once customers buy from your shop, orders will appear here.'],
        'pending' => ['Nothing awaiting your decision', "You're all caught up — no orders need a decision."],
        'confirmed' => ['No confirmed orders', "Orders you've confirmed but not yet dispatched show up here."],
        'ready' => ['Nothing ready', 'Orders out for delivery or set aside for pickup will show up here.'],
        'completed' => ['No completed orders yet', 'Delivered/collected orders will be listed here.'],
        'cancelled' => ['No cancelled orders', 'Nothing has been cancelled.'],
    ];
    [$emptyTitle, $emptyBody] = $emptyCopy[$filter] ?? $emptyCopy['all'];
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Dashboard
            </a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Orders</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $counts['all'] }} total ·
                <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $counts['pending'] }} awaiting your decision</span>
            </p>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('provider.orders.index') }}" class="relative w-full sm:w-72">
            <input type="hidden" name="status" value="{{ $filter }}">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
            </span>
            <input type="search" name="q" value="{{ $search }}" placeholder="Reference, customer, email, product…"
                class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pe-10 ps-10 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-brand-950">
            @if ($search !== '')
                <a href="{{ route('provider.orders.index', ['status' => $filter]) }}"
                   class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 transition hover:text-slate-600" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                </a>
            @endif
        </form>
    </div>

    <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <nav class="flex min-w-max gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900" aria-label="Filter orders by status">
            @foreach ($tabs as $key => $label)
                @php $active = $filter === $key; @endphp
                <a href="{{ route('provider.orders.index', array_filter(['status' => $key, 'q' => $search])) }}"
                   @if ($active) aria-current="page" @endif
                   class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition
                       {{ $active ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                    {{ $label }}
                    @if (($counts[$key] ?? 0) > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold
                            {{ $active ? 'bg-white/25 text-white' : ($key === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}">
                            {{ $counts[$key] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="space-y-3">
        @forelse ($orders as $order)
            @php $needsAction = $order->isPending(); @endphp
            <a href="{{ route('provider.orders.show', $order) }}"
               class="card-lift group relative block overflow-hidden rounded-2xl border bg-white p-5 shadow-sm dark:bg-slate-900
                   {{ $needsAction ? 'border-amber-200 dark:border-amber-900/60' : 'border-slate-200 hover:border-brand-200 dark:border-slate-800 dark:hover:border-brand-800' }}">
                @if ($needsAction)
                    <span aria-hidden="true" class="absolute inset-y-0 start-0 w-1 bg-amber-400"></span>
                @endif

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $order->reference }}</span>
                            <x-order-status :status="$order->status" />
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                {{ $order->isPickup() ? 'Pickup' : 'Delivery' }}
                            </span>
                        </div>
                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                            {{ $order->consumer?->name }} · {{ $order->items->count() }} item(s)
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center justify-between gap-4 sm:justify-end">
                        <div class="text-start sm:text-end">
                            <p class="font-display text-base font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $order->total_amount, 0) }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600 rtl:rotate-180 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7l-8-4-8 4m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">
                    {{ $search !== '' ? 'No matches found' : $emptyTitle }}
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ $search !== '' ? 'Try a different reference, customer, email or product name.' : $emptyBody }}
                </p>
                @if ($search !== '' || $filter !== 'all')
                    <a href="{{ route('provider.orders.index') }}" class="mt-5 inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Clear filters
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if ($orders->hasPages())
        <div>{{ $orders->links() }}</div>
    @endif
</div>
@endsection
