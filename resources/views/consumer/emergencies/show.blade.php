@extends('layouts.app')

@section('title', $emergencyRequest->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('consumer.emergencies.index') }}" />
    </div>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $emergencyRequest->service->name }}</h1>
        <x-emergency-status :status="$emergencyRequest->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Reference {{ $emergencyRequest->reference }}</p>

    {{-- Status banner --}}
    @if ($emergencyRequest->isOpen())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-amber-900 dark:text-amber-300">Reviewing your request…</h2>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">Our team is reviewing your request and will send you a price shortly. This page updates once a quote is ready — refresh to check.</p>
                </div>
            </div>
        </div>
    @elseif ($emergencyRequest->isQuoted())
        <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-900/60 dark:bg-sky-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="flex-1">
                    <h2 class="font-display text-lg font-bold text-sky-900 dark:text-sky-300">Your quote is ready</h2>
                    <p class="mt-1 font-display text-2xl font-extrabold text-sky-900 dark:text-sky-300">Rs. {{ number_format((float) $emergencyRequest->quoted_price, 0) }}</p>
                    @if ($emergencyRequest->admin_note)
                        <p class="mt-1 text-sm text-sky-800 dark:text-sky-400/90">{{ $emergencyRequest->admin_note }}</p>
                    @endif
                    <div class="mt-4 flex gap-3">
                        <form method="POST" action="{{ route('consumer.emergencies.accept-quote', $emergencyRequest) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Accept quote</button>
                        </form>
                        <form method="POST" action="{{ route('consumer.emergencies.decline-quote', $emergencyRequest) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Decline</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($emergencyRequest->isAccepted())
        <div class="mt-6 rounded-2xl border border-violet-200 bg-violet-50 p-6 dark:border-violet-900/60 dark:bg-violet-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-600 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-violet-900 dark:text-violet-300">Finding you a provider…</h2>
                    <p class="mt-1 text-sm text-violet-800 dark:text-violet-400/90">You accepted the Rs. {{ number_format((float) $emergencyRequest->quoted_price, 0) }} quote. We’re assigning a provider now.</p>
                </div>
            </div>
        </div>
    @elseif ($emergencyRequest->isDeclined())
        <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400">You declined this quote.</div>
    @elseif ($emergencyRequest->isMatched())
        <div class="mt-6 rounded-2xl border border-brand-200 bg-brand-50 p-6 dark:border-brand-900/60 dark:bg-brand-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-brand-900 dark:text-brand-300">Provider found — help is on the way</h2>
                    <p class="mt-1 text-sm text-brand-800 dark:text-brand-400/90">
                        {{ $emergencyRequest->matchedProvider?->business_name ?: $emergencyRequest->matchedProvider?->user->name }} accepted your request.
                    </p>
                    @if ($emergencyRequest->booking)
                        <a href="{{ route('consumer.bookings.show', $emergencyRequest->booking) }}" class="mt-3 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">View booking</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400">This request was cancelled.</div>
    @endif

    {{-- Details --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->service->category->name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->city }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->address }}</dd></div>
            @if ($emergencyRequest->notes)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->notes }}</dd></div>
            @endif
        </dl>

        @if ($emergencyRequest->isOpen() || $emergencyRequest->isQuoted())
            <div class="mt-6">
                <x-confirm-form :action="route('consumer.emergencies.cancel', $emergencyRequest)"
                    button-label="Cancel request" button-class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/40"
                    title="Cancel this emergency request?" confirm-label="Cancel request" />
            </div>
        @endif
    </div>
</section>
@endsection