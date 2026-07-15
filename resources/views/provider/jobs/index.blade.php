@extends('layouts.provider')

@section('title', 'Available jobs — ' . config('app.name'))
@section('page_title', 'Available jobs')

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dashboard
        </a>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Available jobs</h1>
            @if ($approved)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-700 dark:bg-brand-950/50 dark:text-brand-400">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-brand-500 animate-ping-ring"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                    </span>
                    Live
                </span>
            @endif
        </div>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Open jobs matching the services you offer. New posts appear instantly.</p>
    </div>

    @if (! $approved)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M12 9v4M12 16.5v.2" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-amber-900 dark:text-amber-300">Verification required</h2>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">You need to be a verified provider before you can view and bid on jobs.</p>
                    <a href="{{ route('provider.onboarding') }}" class="mt-4 inline-flex items-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">Go to verification</a>
                </div>
            </div>
        </div>
    @else
        <div x-data="{
                sort: 'newest',
                hideBid: false,
                myServiceIds: @js($myServiceIds),
                jobs: @js($jobs->map(fn ($job) => [
                    'job_id'       => $job->id,
                    'service_name' => $job->service->name,
                    'category'     => $job->service->category->name,
                    'description'  => \Illuminate\Support\Str::limit($job->description, 110),
                    'city'         => $job->city,
                    'bids_count'   => $job->bids_count,
                    'photos_count' => $job->photos_count,
                    'budget'       => $job->budget !== null ? number_format((float) $job->budget, 0) : null,
                    'budget_raw'   => $job->budget !== null ? (float) $job->budget : null,
                    'preferred'    => $job->preferred_date?->format('D, d M') ?? 'Flexible',
                    'posted'       => $job->created_at->diffForHumans(null, true) . ' ago',
                    'created_ts'   => $job->created_at->timestamp,
                    'url'          => route('provider.jobs.show', $job),
                    'mine'         => $myBids->get($job->id) ? ucfirst($myBids->get($job->id)->status) : null,
                ])),

                get visible() {
                    let list = [...this.jobs];
                    if (this.hideBid) list = list.filter(j => ! j.mine);
                    if (this.sort === 'budget') list.sort((a, b) => (b.budget_raw ?? -1) - (a.budget_raw ?? -1));
                    else if (this.sort === 'bids') list.sort((a, b) => a.bids_count - b.bids_count);
                    else list.sort((a, b) => b.created_ts - a.created_ts);
                    return list;
                },

                chipClasses(status) {
                    return {
                        Pending:   'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                        Accepted:  'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
                        Rejected:  'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
                    }[status] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400';
                },

                init() {
                    if (! window.Echo) return;
                    window.Echo.channel('jobs')
                        .listen('.job.created', (e) => {
                            if (! this.myServiceIds.includes(e.service_id)) return;
                            if (this.jobs.some(j => j.job_id === e.job_id)) return;
                            this.jobs.unshift({ ...e, category: '', mine: null });
                        })
                        .listen('.job.status.updated', (e) => {
                            this.jobs = this.jobs.filter(j => j.job_id !== e.job_id);
                        });
                },
            }">

            {{-- ═══ Toolbar ═══ --}}
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
                <p class="ps-1 text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-bold text-slate-900 dark:text-white" x-text="visible.length"></span>
                    <span x-text="visible.length === 1 ? 'job' : 'jobs'"></span>
                    @if ($myBidsCount > 0)
                        · <span class="font-semibold text-brand-700 dark:text-brand-400">{{ $myBidsCount }} bid on</span>
                    @endif
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" x-model="hideBid"
                            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-400 dark:border-slate-600 dark:bg-slate-800">
                        Hide jobs I've bid on
                    </label>

                    <select x-model="sort"
                        class="rounded-lg border border-slate-200 bg-white py-2 ps-3 pe-8 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <option value="newest">Newest first</option>
                        <option value="budget">Highest budget</option>
                        <option value="bids">Fewest bids</option>
                    </select>
                </div>
            </div>

            {{-- ═══ Cards ═══ --}}
            <div class="mt-4 space-y-3">
                <template x-if="visible.length === 0">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/></svg>
                        </span>
                        <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white"
                           x-text="hideBid && jobs.length > 0 ? 'You\'ve bid on every open job' : 'No matching jobs'"></p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">When customers post jobs for the services you offer, they'll appear here instantly.</p>
                        <a href="{{ route('provider.services.index') }}" class="mt-5 inline-flex items-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Manage my services</a>
                    </div>
                </template>

                <template x-for="job in visible" :key="job.job_id">
                    <a :href="job.url"
                       x-transition:enter="transition ease-out duration-300"
                       x-transition:enter-start="opacity-0 -translate-y-2"
                       x-transition:enter-end="opacity-100 translate-y-0"
                       class="card-lift group block overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-brand-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-sm font-bold text-slate-900 dark:text-white" x-text="job.service_name"></h2>
                                    <template x-if="job.category">
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400" x-text="job.category"></span>
                                    </template>
                                    <template x-if="job.mine">
                                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold" :class="chipClasses(job.mine)" x-text="'Bid ' + job.mine.toLowerCase()"></span>
                                    </template>
                                </div>

                                <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400" x-text="job.description"></p>

                                {{-- Meta pills --}}
                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-400">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11z" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.4"/></svg>
                                        <span x-text="job.city"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
                                        <span x-text="job.preferred"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5" :class="job.bids_count === 0 ? 'font-semibold text-brand-600 dark:text-brand-400' : ''">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M7 8l5-5 5 5M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span x-text="job.bids_count === 0 ? 'Be the first to bid' : job.bids_count + (job.bids_count === 1 ? ' bid' : ' bids')"></span>
                                    </span>
                                    <template x-if="job.photos_count > 0">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="m5 18 5-5 4 4 2-2 3 3" stroke-linejoin="round"/></svg>
                                            <span x-text="job.photos_count"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center justify-between gap-4 sm:flex-col sm:items-end sm:justify-start sm:gap-1">
                                <p class="font-display text-lg font-extrabold text-brand-700 dark:text-brand-400"
                                   x-text="job.budget ? 'Rs. ' + job.budget : 'Open budget'"></p>
                                <p class="text-[11px] text-slate-400" x-text="job.posted"></p>
                                <span class="hidden items-center gap-1 pt-2 text-xs font-semibold text-slate-300 transition group-hover:text-brand-600 sm:inline-flex dark:text-slate-600">
                                    Place bid
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    @endif
</div>
@endsection