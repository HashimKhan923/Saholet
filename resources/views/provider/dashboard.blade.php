@extends('layouts.provider')

@section('title', 'Dashboard — ' . config('app.name'))
@section('page_title', 'Dashboard')

@php
    $status   = $profile?->status ?? 'draft';
    $approved = (bool) $profile?->isApproved();
    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $maxEarn  = max(1, (float) $earningsSeries->max('value'));
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Hero ═══ --}}
    <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <div class="pointer-events-none absolute -end-20 -top-20 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.15em] text-slate-400">{{ now()->format('l, d M Y') }}</p>
                    @if ($approved)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-bold text-brand-700 dark:bg-brand-950/50 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Verified
                        </span>
                    @endif
                </div>

                <h1 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    {{ $greeting }}, {{ auth()->user()->name }}
                </h1>

                <p class="mt-2 max-w-prose text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    @if ($approved)
                        You have <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $activeBookings }}</span> active {{ \Illuminate\Support\Str::plural('booking', $activeBookings) }}
                        and <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $availableJobs }}</span> open {{ \Illuminate\Support\Str::plural('job', $availableJobs) }} matching your services.
                    @else
                        Complete verification to list services, bid on jobs and accept bookings.
                    @endif
                </p>

                @if ($approved && (float) $profile->rating_avg > 0)
                    <div class="mt-3">
                        <x-rating-stars :rating="$profile->rating_avg" :count="$profile->reviews_count" />
                    </div>
                @endif
            </div>

            @if ($approved)
                <div class="flex shrink-0 flex-wrap gap-2.5">
                    <a href="{{ route('provider.jobs.index') }}" class="btn-shine inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/></svg>
                        Browse jobs
                    </a>
                    <a href="{{ route('provider.emergencies.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-red-200 hover:text-red-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-red-800">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z" stroke-linejoin="round"/></svg>
                        Emergencies
                        @if ($openEmergencies > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white">{{ $openEmergencies }}</span>
                        @endif
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ═══ Verification state (unverified only) ═══ --}}
    @unless ($approved)
        @if ($status === 'pending')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
                <div class="flex items-start gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div>
                        <h2 class="font-display text-lg font-bold text-amber-900 dark:text-amber-300">Application under review</h2>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">Our team is reviewing your documents. You'll see your status update here.</p>
                        <a href="{{ route('provider.onboarding') }}" class="mt-3 inline-block text-sm font-semibold text-amber-900 underline dark:text-amber-300">View submission</a>
                    </div>
                </div>
            </div>
        @elseif ($status === 'rejected')
            <div class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-900/60 dark:bg-red-950/30">
                <div class="flex items-start gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500 text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 7l10 10M17 7 7 17" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <h2 class="font-display text-lg font-bold text-red-900 dark:text-red-300">Application needs changes</h2>
                        @if ($profile?->rejection_reason)
                            <p class="mt-1 text-sm text-red-800 dark:text-red-400/90"><span class="font-semibold">Reason:</span> {{ $profile->rejection_reason }}</p>
                        @endif
                        <a href="{{ route('provider.onboarding') }}" class="mt-3 inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Update &amp; resubmit</a>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-start gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M9 12.5l2 2 4-4.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Complete your verification</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add your profile details and upload KYC documents to start earning.</p>
                        </div>
                    </div>
                    <a href="{{ route('provider.onboarding') }}" class="btn-shine inline-flex shrink-0 items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        {{ $profile ? 'Continue' : 'Get started' }}
                    </a>
                </div>
            </div>
        @endif
    @endunless

    @if ($approved)
        {{-- ═══ Headline stats ═══ --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Available balance" :value="$walletAvailable" prefix="Rs. " :href="route('provider.wallet.index')"
                :hint="'Rs. ' . number_format($walletEscrow, 0) . ' held in escrow'" tone="brand">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/></svg>
            </x-stat-card>

            <x-stat-card label="Earned this month" :value="$earningsMonth" prefix="Rs. " :delta="$earningsDelta"
                :hint="'Rs. ' . number_format($earningsTotal, 0) . ' all time'" tone="violet">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18V9M10 18V5M16 18v-6M20 18h-.01M4 21h16" stroke-linecap="round"/></svg>
            </x-stat-card>

            <x-stat-card label="Jobs completed" :value="$jobsCompleted" :href="route('provider.bookings.index')"
                :hint="$activeBookings . ' active right now'" tone="sky">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7 9.5 17.5 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </x-stat-card>

            <x-stat-card label="Average rating" :value="$profile->rating_avg" :decimals="1" suffix=" / 5"
                :hint="$profile->reviews_count . ' ' . \Illuminate\Support\Str::plural('review', $profile->reviews_count)" tone="amber">
                <svg viewBox="0 0 20 20" class="h-5 w-5" fill="currentColor"><path d="M10 1.6l2.5 5.1 5.6.8-4 3.9 1 5.6L10 14.4 5 17l1-5.6-4-3.9 5.6-.8L10 1.6z"/></svg>
            </x-stat-card>
        </section>

        {{-- ═══ Performance ═══ --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Performance</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">How customers experience you on Sahoulat.</p>

            <div class="mt-5 grid gap-6 sm:grid-cols-3">
                @php
                    $metrics = [
                        ['Completion rate', $completionRate, '%', 'Bookings finished vs cancelled', 'bg-brand-500'],
                        ['Bid win rate', $bidWinRate, '%', $bidsPending . ' ' . \Illuminate\Support\Str::plural('bid', $bidsPending) . ' awaiting a decision', 'bg-sky-500'],
                    ];
                @endphp

                @foreach ($metrics as [$label, $val, $unit, $hint, $bar])
                    <div>
                        <div class="flex items-baseline justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                            <p class="font-display text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ is_null($val) ? '—' : $val . $unit }}
                            </p>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full {{ $bar }} transition-[width] duration-700 ease-out" style="width: {{ (int) ($val ?? 0) }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</p>
                    </div>
                @endforeach

                <div>
                    <div class="flex items-baseline justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Response time</p>
                        <p class="font-display text-lg font-extrabold text-slate-900 dark:text-white">
                            @if (is_null($responseMinutes))
                                —
                            @elseif ($responseMinutes < 60)
                                {{ $responseMinutes }}<span class="text-sm text-slate-400"> min</span>
                            @else
                                {{ round($responseMinutes / 60, 1) }}<span class="text-sm text-slate-400"> hrs</span>
                            @endif
                        </p>
                    </div>
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        {{-- Faster = fuller bar. 5 minutes or less fills the bar; 4 hours empties it. --}}
                        <div class="h-full rounded-full bg-violet-500 transition-[width] duration-700 ease-out"
                            style="width: {{ is_null($responseMinutes) ? 0 : max(4, min(100, (int) round((1 - min($responseMinutes, 240) / 240) * 100))) }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Average time to confirm a booking</p>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- ═══ Left: schedule + earnings ═══ --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Today's schedule --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <div>
                            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Today's schedule</h2>
                            <p class="mt-0.5 text-xs text-slate-400">{{ now()->format('D, d M') }}</p>
                        </div>
                        <a href="{{ route('provider.bookings.index') }}" class="text-sm font-semibold text-brand-700 transition hover:text-brand-800 dark:text-brand-400">All bookings</a>
                    </div>

                    @if ($todaySchedule->isEmpty())
                        <div class="px-6 py-12 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
                            </span>
                            <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Nothing scheduled today</p>
                            <p class="mt-1 text-sm text-slate-400">Bid on open jobs to fill your calendar.</p>
                            <a href="{{ route('provider.jobs.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Find jobs</a>
                        </div>
                    @else
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($todaySchedule as $booking)
                                <li>
                                    <a href="{{ route('provider.bookings.show', $booking) }}" class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <div class="w-16 shrink-0 text-center">
                                            <p class="font-display text-sm font-extrabold text-slate-900 dark:text-white">{{ \Illuminate\Support\Carbon::parse($booking->scheduled_time)->format('g:i') }}</p>
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ \Illuminate\Support\Carbon::parse($booking->scheduled_time)->format('A') }}</p>
                                        </div>
                                        <div class="h-10 w-px shrink-0 bg-slate-200 dark:bg-slate-700"></div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->service?->name ?? 'Service' }}</p>
                                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $booking->consumer?->name }} · {{ $booking->address }}</p>
                                        </div>
                                        <div class="hidden shrink-0 text-end sm:block">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">Rs. {{ number_format((float) $booking->price, 0) }}</p>
                                            <div class="mt-1"><x-booking-status :status="$booking->status" /></div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Earnings, last 6 months --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Earnings</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Last 6 months, after commission</p>
                        </div>
                        <p class="font-display text-xl font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format($earningsTotal, 0) }}</p>
                    </div>

                    <div class="mt-6 flex h-40 items-end gap-2 sm:gap-4">
                        @foreach ($earningsSeries as $point)
                            @php $pct = (int) round(($point['value'] / $maxEarn) * 100); @endphp
                            <div class="group flex flex-1 flex-col items-center gap-2">
                                <p class="text-[10px] font-bold text-slate-400 opacity-0 transition group-hover:opacity-100">
                                    {{ $point['value'] > 0 ? number_format($point['value'] / 1000, 1) . 'k' : '0' }}
                                </p>
                                <div class="flex w-full flex-1 items-end">
                                    <div class="w-full rounded-t-lg bg-gradient-to-t from-brand-600 to-brand-400 transition-all duration-500 ease-out group-hover:from-brand-700 group-hover:to-brand-500"
                                        style="height: {{ max($pct, 2) }}%" role="img"
                                        aria-label="{{ $point['label'] }}: Rs. {{ number_format($point['value'], 0) }}"></div>
                                </div>
                                <p class="text-[11px] font-semibold text-slate-400">{{ $point['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- ═══ Right: activity + quick actions ═══ --}}
            <div class="space-y-6">

                {{-- Activity feed --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Recent activity</h2>
                    </div>

                    @if ($activity->isEmpty())
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm text-slate-400">No activity yet. Your bookings, bids and payouts will show up here.</p>
                        </div>
                    @else
                        @php
                            $feedTones = [
                                'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400',
                                'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
                                'sky'   => 'bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400',
                                'red'   => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
                                'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                            ];
                        @endphp
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($activity as $item)
                                <li>
                                    <a href="{{ $item['url'] }}" class="flex items-start gap-3 px-5 py-3.5 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $feedTones[$item['tone']] ?? $feedTones['slate'] }}">
                                            @if ($item['icon'] === 'earning')
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3v18M16 7H10a3 3 0 000 6h4a3 3 0 010 6H8" stroke-linecap="round"/></svg>
                                            @elseif ($item['icon'] === 'bid')
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3v18M7 8l5-5 5 5M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @else
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold capitalize text-slate-900 dark:text-white">{{ $item['title'] }}</p>
                                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $item['meta'] }}</p>
                                        </div>
                                        <time class="shrink-0 text-[11px] font-medium text-slate-300 dark:text-slate-600" datetime="{{ $item['at']->toIso8601String() }}">
                                            {{ $item['at']->diffForHumans(null, true) }}
                                        </time>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Quick actions --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Quick actions</h2>

                    @php
                        $actions = [
                            ['Available jobs', route('provider.jobs.index'), $availableJobs, 'sky'],
                            ['Pending bookings', route('provider.bookings.index'), $pendingBookings, 'amber'],
                            ['My bids', route('provider.bids.index'), $bidsPending, 'slate'],
                            ['My services', route('provider.services.index'), 0, 'slate'],
                        ];
                        $badgeTones = [
                            'sky'   => 'bg-sky-500',
                            'amber' => 'bg-amber-500',
                            'slate' => 'bg-slate-400',
                        ];
                    @endphp

                    <div class="mt-3 space-y-1">
                        @foreach ($actions as [$label, $url, $badge, $tone])
                            <a href="{{ $url }}" class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                                <span>{{ $label }}</span>
                                <span class="flex items-center gap-2">
                                    @if ($badge > 0)
                                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full {{ $badgeTones[$tone] }} px-1.5 text-[11px] font-bold text-white">{{ $badge }}</span>
                                    @endif
                                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-300 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    @endif
</div>
@endsection