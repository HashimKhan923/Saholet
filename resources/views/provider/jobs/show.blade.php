@extends('layouts.provider')

@section('title', $jobPost->reference . ' — ' . config('app.name'))
@section('page_title', 'Job detail')

@php
    // Whether I'M the provider whose bid won — they should never see the
    // "taken by another provider" banner about their own accepted bid.
    $myProviderProfileId = $myBid->provider_profile_id ?? null;
    $iWonThisJob = $myBid && $myBid->isAccepted();
@endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8"
    x-data="{
        jobTaken: {{ (! $jobPost->isOpen() && ! $iWonThisJob) ? 'true' : 'false' }},
        myProviderProfileId: {{ $myProviderProfileId ?? 'null' }},
        init() {
            if (! window.Echo) return;
            window.Echo.channel('jobs').listen('.job.status.updated', (e) => {
                if (e.job_id === {{ $jobPost->id }} && e.status !== 'open' && e.accepted_provider_profile_id !== this.myProviderProfileId) {
                    this.jobTaken = true;
                }
            });
        },
    }">

    {{-- ═══ Header ═══ --}}
    <div>
        <a href="{{ route('provider.jobs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Available jobs
        </a>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $jobPost->service->name }}</h1>
            <x-job-status :status="$jobPost->status" />
        </div>
        <p class="mt-1 font-mono text-sm text-slate-400">{{ $jobPost->reference }}</p>
    </div>

    <div x-show="jobTaken" x-cloak class="mt-4 flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-400">
        <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16v.2" stroke-linecap="round"/></svg>
        This job was just taken by another provider.
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        {{-- ═══ Left: job details ═══ --}}
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Job details</h2>

                <dl class="mt-5 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Category</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->service->category->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Customer budget</dt>
                        <dd class="mt-1 font-display text-lg font-extrabold text-brand-700 dark:text-brand-400">
                            {{ $jobPost->budget ? 'Rs. ' . number_format((float) $jobPost->budget, 0) : 'Open' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Preferred date</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->preferred_date?->format('D, d M Y') ?? 'Flexible' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">City</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->city }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Address</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->address }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Description</dt>
                        <dd class="mt-1 leading-relaxed text-slate-700 dark:text-slate-300">{{ $jobPost->description }}</dd>
                    </div>
                </dl>

                @if ($jobPost->photos->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-6 dark:border-slate-800">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Photos from the customer</p>
                        <x-photo-gallery :photos="$jobPost->photos" />
                    </div>
                @endif
            </section>
        </div>

        {{-- ═══ Right: bid panel ═══ --}}
        <div>
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-24">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Your bid</h2>

                @if ($myBid && ! $myBid->isPending())
                    {{-- Settled: accepted / rejected / withdrawn --}}
                    <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <x-bid-status :status="$myBid->status" />
                        <p class="mt-3 font-display text-xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $myBid->amount, 0) }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $myBid->dateLabel() }} at {{ $myBid->timeLabel() }}</p>
                    </div>

                    @if ($myBid->isAccepted() && $myBid->booking)
                        <a href="{{ route('provider.bookings.show', $myBid->booking) }}" class="btn-shine mt-4 inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">View booking</a>
                    @elseif ($myBid->isRejected())
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">The customer went with another provider this time.</p>
                    @endif

                @elseif (! $jobPost->isOpen())
                    <p class="mt-4 rounded-xl bg-slate-50 px-4 py-3.5 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">This job is no longer open for bids.</p>

                @elseif (! $offersService)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-400">
                        You don't currently offer this service, so you can't bid.
                        <a href="{{ route('provider.services.index') }}" class="font-semibold underline underline-offset-2">Add it to your services</a> first.
                    </div>

                @else
                    @php
                        $action = $myBid
                            ? route('provider.bids.update', $myBid)
                            : route('provider.jobs.bids.store', $jobPost);
                    @endphp

                    <p x-show="jobTaken" x-cloak class="mt-4 rounded-xl bg-slate-50 px-4 py-3.5 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">This job is no longer open for bids.</p>

                    <form x-show="! jobTaken" method="POST" action="{{ $action }}" class="mt-4 space-y-4">
                        @csrf
                        @if ($myBid) @method('PUT') @endif

                        <div>
                            <label for="amount" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Your price</label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-sm font-semibold text-slate-400">Rs.</span>
                                <input id="amount" name="amount" type="number" step="1" min="0" required inputmode="numeric"
                                    value="{{ old('amount', $myBid ? (int) $myBid->amount : '') }}"
                                    class="block w-full rounded-xl border border-slate-200 py-2.5 pe-3.5 ps-11 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            </div>
                            @if ($jobPost->budget)
                                <p class="mt-1.5 text-xs text-slate-400">Customer budgeted Rs. {{ number_format((float) $jobPost->budget, 0) }}</p>
                            @endif
                            <x-field-error name="amount" />
                        </div>

                        <div>
                            <label for="proposed_date" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Date</label>
                            <input id="proposed_date" name="proposed_date" type="date" required min="{{ now()->toDateString() }}"
                                value="{{ old('proposed_date', $myBid?->proposed_date?->toDateString()) }}"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <x-field-error name="proposed_date" />
                        </div>

                        <div>
                            <label for="proposed_time" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Time</label>
                            @php $currentTime = old('proposed_time', $myBid ? substr($myBid->proposed_time, 0, 5) : ''); @endphp
                            <select id="proposed_time" name="proposed_time" required
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">—</option>
                                @foreach ($slots as $slot)
                                    <option value="{{ $slot['value'] }}" @selected($currentTime === $slot['value'])>{{ $slot['label'] }}</option>
                                @endforeach
                            </select>
                            <x-field-error name="proposed_time" />
                        </div>

                        <div>
                            <label for="message" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Message <span class="normal-case text-slate-400">(optional)</span></label>
                            <textarea id="message" name="message" rows="3" placeholder="Why you're the right fit…"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">{{ old('message', $myBid?->message) }}</textarea>
                            <x-field-error name="message" />
                        </div>

                        <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            {{ $myBid ? 'Update bid' : 'Submit bid' }}
                        </button>

                        @if ($myBid)
                            <p class="text-center text-xs text-slate-400">Pending — you can edit or withdraw.</p>
                        @endif
                    </form>

                    @if ($myBid)
                        <div class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-800" x-show="! jobTaken">
                            <x-confirm-form :action="route('provider.bids.destroy', $myBid)" method="DELETE"
                                button-label="Withdraw bid"
                                button-class="w-full rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30"
                                title="Withdraw your bid?" confirm-label="Withdraw bid" />
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </div>
</div>
@endsection