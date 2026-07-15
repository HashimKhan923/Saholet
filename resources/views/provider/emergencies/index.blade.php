@extends('layouts.provider')

@section('title', 'Emergency requests — ' . config('app.name'))
@section('page_title', 'Emergencies')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dashboard
        </a>

        <div class="mt-1 flex flex-wrap items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z" stroke-linejoin="round"/></svg>
            </span>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Emergency requests</h1>

            @if ($approved)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-red-500 animate-ping-ring"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    </span>
                    Live
                </span>
            @endif
        </div>

        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            Urgent jobs in <span class="font-semibold text-slate-700 dark:text-slate-200">{{ ucwords($myCity) ?: 'your city' }}</span> matching your services.
            <span class="font-semibold text-red-600 dark:text-red-400">First to accept wins</span> — accepting creates a confirmed booking immediately.
        </p>
    </div>

    @if (! $approved)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M12 9v4M12 16.5v.2" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-amber-900 dark:text-amber-300">Verification required</h2>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">You need to be a verified provider before you can take emergency requests.</p>
                    <a href="{{ route('provider.onboarding') }}" class="mt-4 inline-flex items-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">Go to verification</a>
                </div>
            </div>
        </div>
    @else
        <div x-data="{
                now: Math.floor(Date.now() / 1000),
                profileId: {{ $profileId }},
                myCity: @js($myCity),
                myServiceIds: @js($myServiceIds),
                myPrices: @js($myPrices),
                requests: @js($requests->map(fn ($r) => [
                    'id'           => $r->id,
                    'reference'    => $r->reference,
                    'service_id'   => $r->service_id,
                    'service_name' => $r->service->name,
                    'category'     => $r->service->category->name,
                    'city'         => $r->city,
                    'address'      => $r->address,
                    'notes'        => $r->notes,
                    'created_ts'   => $r->created_at->timestamp,
                    'accept_url'   => route('provider.emergencies.accept', $r),
                    'taken'        => false,
                ])),

                /* Seconds → 'just now' / '4m waiting' / '1h 12m waiting' */
                waited(ts) {
                    const s = Math.max(0, this.now - ts);
                    const m = Math.floor(s / 60);
                    if (m < 1) return 'just now';
                    if (m < 60) return m + 'm waiting';
                    return Math.floor(m / 60) + 'h ' + (m % 60) + 'm waiting';
                },

                /* A request nobody has taken after 10 minutes deserves visual escalation. */
                stale(ts) { return (this.now - ts) > 600; },

                payout(serviceId) { return this.myPrices[serviceId] ?? null; },

                init() {
                    setInterval(() => { this.now = Math.floor(Date.now() / 1000); }, 1000);

                    if (! window.Echo) return;

                    // Full detail (address, notes) arrives only on our own private channel.
                    window.Echo.private('provider.' + this.profileId)
                        .listen('.emergency.created', (e) => {
                            if (this.requests.some(r => r.id === e.id)) return;
                            this.requests.unshift({ ...e, taken: false });
                        });

                    // Public: id + status only. Someone took it, or the customer cancelled.
                    window.Echo.channel('emergencies')
                        .listen('.emergency.status.updated', (e) => {
                            if (e.status === 'open') return;
                            const req = this.requests.find(r => r.id === e.id);
                            if (! req) return;
                            req.taken = true;
                            setTimeout(() => {
                                this.requests = this.requests.filter(r => r.id !== e.id);
                            }, 2600);
                        });
                },
            }">

            {{-- Count strip --}}
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-bold text-slate-900 dark:text-white" x-text="requests.filter(r => ! r.taken).length"></span>
                    open <span x-text="requests.filter(r => ! r.taken).length === 1 ? 'request' : 'requests'"></span>
                </p>
                <p class="text-xs text-slate-400">Updates in real time — no need to refresh</p>
            </div>

            {{-- Cards --}}
            <div class="mt-4 space-y-3">
                <template x-if="requests.length === 0">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z" stroke-linejoin="round"/></svg>
                        </span>
                        <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">All quiet right now</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Urgent jobs in your city for the services you offer will appear here the moment they're posted.</p>
                        <a href="{{ route('provider.services.index') }}" class="mt-5 inline-flex items-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Manage my services</a>
                    </div>
                </template>

                <template x-for="req in requests" :key="req.id">
                    <div x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="relative overflow-hidden rounded-2xl border bg-white p-5 shadow-sm transition dark:bg-slate-900"
                         :class="req.taken
                            ? 'border-slate-200 opacity-60 dark:border-slate-800'
                            : (stale(req.created_ts) ? 'border-red-300 dark:border-red-900' : 'border-slate-200 dark:border-slate-800')">

                        {{-- Urgency rail --}}
                        <span aria-hidden="true" class="absolute inset-y-0 start-0 w-1"
                              :class="req.taken ? 'bg-slate-300 dark:bg-slate-700' : 'bg-red-500'"></span>

                        {{-- Taken overlay --}}
                        <template x-if="req.taken">
                            <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 backdrop-blur-sm dark:bg-slate-900/80">
                                <p class="flex items-center gap-2 text-sm font-bold text-slate-500 dark:text-slate-400">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M7 7l10 10M17 7 7 17" stroke-linecap="round"/></svg>
                                    Taken by another provider
                                </p>
                            </div>
                        </template>

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1 ps-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-sm font-bold text-slate-900 dark:text-white" x-text="req.service_name"></h2>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400" x-text="req.category"></span>

                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                          :class="stale(req.created_ts)
                                            ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'">
                                        <span class="h-1.5 w-1.5 rounded-full animate-live-pulse"
                                              :class="stale(req.created_ts) ? 'bg-red-500' : 'bg-amber-500'"></span>
                                        <span x-text="waited(req.created_ts)"></span>
                                    </span>
                                </div>

                                <p class="mt-2 flex items-start gap-1.5 text-sm text-slate-600 dark:text-slate-300">
                                    <svg viewBox="0 0 24 24" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11z" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.4"/></svg>
                                    <span x-text="req.address"></span>
                                </p>

                                <template x-if="req.notes">
                                    <p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-relaxed text-slate-600 dark:bg-slate-800 dark:text-slate-400" x-text="req.notes"></p>
                                </template>

                                <p class="mt-2 font-mono text-[11px] text-slate-400" x-text="req.reference"></p>
                            </div>

                            {{-- Payout + accept --}}
                            <div class="flex shrink-0 flex-col items-stretch gap-2 sm:w-40 sm:items-end">
                                <template x-if="payout(req.service_id) !== null">
                                    <div class="text-start sm:text-end">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">You'd earn</p>
                                        <p class="font-display text-xl font-extrabold text-brand-700 dark:text-brand-400">
                                            Rs. <span x-text="payout(req.service_id).toLocaleString()"></span>
                                        </p>
                                    </div>
                                </template>

                                <form method="POST" :action="req.accept_url" x-data="{ submitting: false }" @submit="submitting = true">
                                    <input type="hidden" name="_token" :value="$el.closest('body').querySelector('meta[name=csrf-token]').content">
                                    <button type="submit" :disabled="submitting || req.taken"
                                        class="btn-shine w-full rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                                        <span x-show="! submitting">Accept now</span>
                                        <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            Accepting…
                                        </span>
                                    </button>
                                </form>

                                <p class="text-center text-[10px] leading-tight text-slate-400 sm:text-end">Creates a confirmed booking</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    @endif
</div>
@endsection