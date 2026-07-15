@extends('layouts.provider')

@section('title', 'My bids — ' . config('app.name'))
@section('page_title', 'My bids')

@php
    $tabs = [
        'all'       => 'All',
        'pending'   => 'Pending',
        'accepted'  => 'Accepted',
        'rejected'  => 'Rejected',
        'withdrawn' => 'Withdrawn',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dashboard
        </a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My bids</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track everything you've proposed and how it landed.</p>
    </div>

    {{-- ═══ Summary ═══ --}}
    @if ($counts['all'] > 0)
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">In the pipeline</p>
                <p class="mt-1 font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($pipeline, 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $counts['pending'] }} pending {{ \Illuminate\Support\Str::plural('bid', $counts['pending']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Win rate</p>
                <p class="mt-1 font-display text-2xl font-extrabold text-slate-900 dark:text-white">{{ is_null($winRate) ? '—' : $winRate . '%' }}</p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-brand-500 transition-[width] duration-700 ease-out" style="width: {{ (int) ($winRate ?? 0) }}%"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jobs won</p>
                <p class="mt-1 font-display text-2xl font-extrabold text-brand-700 dark:text-brand-400">{{ $counts['accepted'] }}</p>
                <p class="mt-1 text-xs text-slate-400">out of {{ $counts['all'] }} {{ \Illuminate\Support\Str::plural('bid', $counts['all']) }} placed</p>
            </div>
        </div>
    @endif

    {{-- ═══ Status tabs ═══ --}}
    @if ($counts['all'] > 0)
        <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
            <nav class="flex min-w-max gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900" aria-label="Filter bids by status">
                @foreach ($tabs as $key => $label)
                    @php $active = $filter === $key; @endphp
                    <a href="{{ route('provider.bids.index', $key === 'all' ? [] : ['status' => $key]) }}"
                       @if ($active) aria-current="page" @endif
                       class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition
                           {{ $active
                               ? 'bg-brand-600 text-white shadow-sm'
                               : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                        {{ $label }}
                        @if (($counts[$key] ?? 0) > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold
                                {{ $active ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                {{ $counts[$key] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    {{-- ═══ List ═══ --}}
    <div class="space-y-3">
        @forelse ($bids as $bid)
            @php $won = $bid->isAccepted(); @endphp
            <div class="relative overflow-hidden rounded-2xl border bg-white p-5 shadow-sm transition dark:bg-slate-900
                {{ $won ? 'border-brand-200 dark:border-brand-900/60' : 'border-slate-200 dark:border-slate-800' }}">

                @if ($won)
                    <span aria-hidden="true" class="absolute inset-y-0 start-0 w-1 bg-brand-500"></span>
                @endif

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $bid->jobPost->service->name }}</span>
                            <x-bid-status :status="$bid->status" />
                        </div>
                        <p class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            <span class="font-display text-sm font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $bid->amount, 0) }}</span>
                            <span>·</span>
                            <span>{{ $bid->dateLabel() }} at {{ $bid->timeLabel() }}</span>
                        </p>
                        <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $bid->jobPost->reference }} · placed {{ $bid->created_at->diffForHumans() }}</p>

                        @if ($bid->message)
                            <p class="mt-2 line-clamp-1 max-w-lg text-xs italic text-slate-400">"{{ $bid->message }}"</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2.5">
                        @if ($won && $bid->booking)
                            <a href="{{ route('provider.bookings.show', $bid->booking) }}" class="btn-shine rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">View booking</a>
                        @endif
                        <a href="{{ route('provider.jobs.show', $bid->jobPost) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            {{ $bid->isPending() ? 'Edit bid' : 'View job' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3v18M7 8l5-5 5 5M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">
                    {{ $filter === 'all' ? 'No bids yet' : 'Nothing here' }}
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ $filter === 'all' ? 'Browse available jobs and place your first bid.' : 'No bids with this status.' }}
                </p>
                <a href="{{ $filter === 'all' ? route('provider.jobs.index') : route('provider.bids.index') }}"
                   class="mt-5 inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                    {{ $filter === 'all' ? 'Find jobs' : 'Show all bids' }}
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection