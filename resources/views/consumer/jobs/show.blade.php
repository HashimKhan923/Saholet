@extends('layouts.app')

@section('title', $jobPost->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.jobs.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My jobs</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $jobPost->service->name }}</h1>
        <x-job-status :status="$jobPost->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Reference {{ $jobPost->reference }}</p>

    {{-- Job details --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->service->category->name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Budget</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->budget ? 'Rs. ' . number_format($jobPost->budget, 0) : 'Open' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Preferred date</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->preferred_date?->format('D, d M Y') ?? 'Flexible' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->city }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Service address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->address }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Description</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $jobPost->description }}</dd></div>
        </dl>

        <x-photo-gallery :photos="$jobPost->photos" />

        @if ($jobPost->isOpen())
            <div class="mt-6">
                <x-confirm-form :action="route('consumer.jobs.cancel', $jobPost)"
                    button-label="Cancel job" button-class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/40"
                    title="Cancel this job?" message="All pending bids will be rejected." confirm-label="Cancel job" />
            </div>
        @endif
    </div>

    {{-- Awarded summary --}}
    @if ($jobPost->isAwarded())
        @php $accepted = $jobPost->acceptedBid(); @endphp
        @if ($accepted)
            <div class="mt-6 rounded-2xl border border-brand-200 bg-brand-50 p-6 dark:border-brand-900/60 dark:bg-brand-950/30">
                <h2 class="font-display text-lg font-bold text-brand-900 dark:text-brand-300">Bid accepted</h2>
                <p class="mt-1 text-sm text-brand-800 dark:text-brand-400/90">
                    You accepted {{ $accepted->providerProfile->business_name ?: $accepted->providerProfile->user->name }}’s
                    bid of Rs. {{ number_format($accepted->amount, 0) }} for {{ $accepted->dateLabel() }} at {{ $accepted->timeLabel() }}.
                </p>
                @if ($accepted->booking)
                    <a href="{{ route('consumer.bookings.show', $accepted->booking) }}" class="mt-3 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">View booking</a>
                @endif
            </div>
        @endif
    @endif

    {{-- Bids --}}
    <div class="mt-8"
        x-data="{
            bids: @js($jobPost->bids->map(fn ($bid) => [
                'id' => $bid->id,
                'status' => $bid->status,
                'status_label' => ucfirst($bid->status),
                'status_classes' => match ($bid->status) {
                    'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                    'accepted' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
                    default => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                },
                'can_accept' => $jobPost->isOpen() && $bid->isPending(),
                'provider_name' => $bid->providerProfile->business_name ?: $bid->providerProfile->user->name,
                'city' => $bid->providerProfile->city ?: 'Pakistan',
                'experience_years' => $bid->providerProfile->experience_years,
                'amount' => number_format((float) $bid->amount, 0),
                'date_label' => $bid->dateLabel(),
                'time_label' => $bid->timeLabel(),
                'message' => $bid->message,
            ])),
            init() {
                if (! window.Echo) return;
                window.Echo.private('job.{{ $jobPost->id }}').listen('.bid.updated', (e) => {
                    const i = this.bids.findIndex(b => b.id === e.id);
                    if (i === -1) {
                        this.bids.unshift(e);
                    } else {
                        this.bids[i] = e;
                    }
                });
            },
        }">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Bids</h2>

        <div class="mt-4 space-y-3">
            <template x-if="bids.length === 0">
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No bids yet</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Providers who offer this service will be able to bid.</p>
                </div>
            </template>

            <template x-for="bid in bids" :key="bid.id">
                <div x-data="{ open: false, submitting: false }"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 font-display text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                x-text="bid.provider_name.charAt(0).toUpperCase()"></span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="bid.provider_name"></p>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="bid.status_classes" x-text="bid.status_label"></span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400" x-text="bid.city + ' · ' + bid.experience_years + ' yr experience'"></p>
                                <p class="mt-2 text-xs text-slate-600 dark:text-slate-300" x-text="'Proposed: ' + bid.date_label + ' at ' + bid.time_label"></p>
                                <p x-show="bid.message" class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300" x-text="bid.message"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-lg font-extrabold text-slate-900 dark:text-white" x-text="'Rs. ' + bid.amount"></p>
                        </div>
                    </div>

                    <div class="mt-4" x-show="bid.can_accept">
                        <button type="button" @click="open = true" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Accept bid</button>

                        <form method="POST" :action="'{{ url('jobs') }}/{{ $jobPost->id }}/bids/' + bid.id + '/accept'" x-ref="acceptForm" class="hidden" @submit="submitting = true">
                            @csrf
                        </form>

                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="fixed inset-0 bg-slate-900/50" x-transition.opacity @click="open = false"></div>
                            <div x-show="open" x-transition class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
                                <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">Accept this bid?</h3>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">This creates a confirmed booking and rejects other bids.</p>
                                <div class="mt-6 flex justify-end gap-2">
                                    <button type="button" @click="open = false" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Never mind</button>
                                    <button type="button" :disabled="submitting" @click="submitting = true; $refs.acceptForm.submit()"
                                        class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                                        <span x-show="!submitting">Accept bid</span>
                                        <span x-show="submitting" x-cloak>Please wait…</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
@endsection