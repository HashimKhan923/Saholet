@extends('layouts.app')

@section('title', $contract->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.contracts.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My contracts</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $contract->title }}</h1>
        <x-contract-status :status="$contract->status" />
    </div>
    <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500 dark:text-slate-400">Reference {{ $contract->reference }}</p>
        @if (in_array($contract->status, [\App\Models\Contract::STATUS_ACCEPTED, \App\Models\Contract::STATUS_IN_PROGRESS, \App\Models\Contract::STATUS_COMPLETED], true))
            <a href="{{ route('contracts.invoice', $contract) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300">
                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Download invoice
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    {{-- Project details --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->city }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Preferred start</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->preferred_start_date?->format('D, d M Y') ?? 'Flexible' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Site address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->address }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Description</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->description }}</dd></div>
        </dl>

        <x-photo-gallery :photos="$contract->photos" />

        @if ($contract->isCancellable())
            <div class="mt-6">
                <x-confirm-form :action="route('consumer.contracts.cancel', $contract)"
                    button-label="Cancel contract" button-class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/40"
                    title="Cancel this contract?" message="This cannot be undone." confirm-label="Cancel contract" />
            </div>
        @endif
    </div>

    {{-- Quote decision --}}
    @if ($contract->isQuoted())
        <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-900/60 dark:bg-sky-950/30">
            <h2 class="font-display text-lg font-bold text-sky-900 dark:text-sky-300">Your quote is ready</h2>
            @if ($contract->admin_notes)
                <p class="mt-2 text-sm text-sky-800 dark:text-sky-400/90">{{ $contract->admin_notes }}</p>
            @endif
            <p class="mt-3 font-display text-2xl font-extrabold text-sky-900 dark:text-sky-300">Rs. {{ number_format($contract->quoted_total, 0) }}</p>
            <div class="mt-4 flex gap-3">
                <x-confirm-form :action="route('consumer.contracts.accept', $contract)"
                    button-label="Accept quote" button-class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                    title="Accept this quote?" message="You'll then be able to pay the first milestone to get started." confirm-label="Accept" confirm-class="bg-brand-600 hover:bg-brand-700" />
                <x-confirm-form :action="route('consumer.contracts.reject', $contract)"
                    button-label="Reject quote" button-class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    title="Reject this quote?" message="You can discuss changes with our team afterwards." confirm-label="Reject" />
            </div>
        </div>
    @endif

    {{-- Items --}}
    <div class="mt-8">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Services in this contract</h2>
        <div class="mt-4 space-y-3">
            @foreach ($contract->items as $item)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->service->name }} <span class="font-normal text-slate-400 dark:text-slate-500">&times;{{ $item->quantity }}</span></p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $item->service->category->name }}</p>
                            @if ($item->notes)
                                <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">{{ $item->notes }}</p>
                            @endif
                            @if ($item->providerProfile)
                                <p class="mt-2 text-xs font-medium text-brand-700 dark:text-brand-400">
                                    Assigned to {{ $item->providerProfile->business_name ?: $item->providerProfile->user->name }}
                                    @if ($item->booking)
                                        &middot; <a href="{{ route('consumer.bookings.show', $item->booking) }}" class="underline hover:text-brand-800 dark:hover:text-brand-300">View booking</a>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if ($item->quoted_price)
                                <p class="font-display text-base font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($item->quoted_price, 0) }}</p>
                            @endif
                            <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Milestones --}}
    @if ($contract->milestones->isNotEmpty())
        <div class="mt-8">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Payment schedule</h2>
            <div class="mt-4 space-y-3">
                @foreach ($contract->milestones as $milestone)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $milestone->title }}</p>
                            @if ($milestone->description)
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $milestone->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="font-display text-base font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($milestone->amount, 0) }}</p>
                            <x-payment-status :status="$milestone->status" />
                            @if ($milestone->isPayable())
                                <a href="{{ route('consumer.contracts.milestones.pay', [$contract, $milestone]) }}"
                                   class="rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">Pay now</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
