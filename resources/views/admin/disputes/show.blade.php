@extends('layouts.admin')

@section('title', 'Review dispute — ' . config('app.name'))

@section('content')
@php $payment = $dispute->booking->activePayment(); @endphp
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.disputes.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Disputes</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Dispute {{ $dispute->reference }}</h1>
        <x-dispute-status :status="$dispute->status" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Booking</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Service</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $dispute->booking->service->name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Reference</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $dispute->booking->reference }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Customer</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $dispute->booking->consumer->name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $dispute->booking->providerProfile->business_name ?: $dispute->booking->providerProfile->user->name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Booking status</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', $dispute->booking->status)) }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Amount</dt><dd class="font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($dispute->booking->price, 0) }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Complaint</h2>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Opened by {{ $dispute->opener->name }} ({{ $dispute->opened_by_role }}) on {{ $dispute->created_at->format('d M Y, g:i A') }}</p>
                <p class="mt-3 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $dispute->reason }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Payment</h2>
                @if ($payment && $payment->isEscrow())
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400"><x-payment-status status="escrow" /> &nbsp; Rs. {{ number_format($payment->amount, 0) }} is held in escrow. Resolving will release or refund it.</p>
                @elseif ($payment && $payment->isReleased())
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400"><x-payment-status status="released" /> &nbsp; Already released — resolving will not move money.</p>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No escrow payment (cash/COD). Resolving records a decision without moving money.</p>
                @endif
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Resolve</h2>

                @if ($dispute->isOpen())
                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="mt-4 space-y-4" x-data="{ resolution: 'release' }">
                        @csrf
                        <div class="space-y-2">
                            <label class="flex cursor-pointer items-start gap-2 rounded-lg border p-3 transition" :class="resolution === 'release' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/40' : 'border-slate-200 dark:border-slate-700'">
                                <input type="radio" name="resolution" value="release" x-model="resolution" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                                <span><span class="block text-sm font-semibold text-slate-900 dark:text-white">Release to provider</span><span class="block text-xs text-slate-500 dark:text-slate-400">Provider keeps the payment.</span></span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-2 rounded-lg border p-3 transition" :class="resolution === 'refund' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/40' : 'border-slate-200 dark:border-slate-700'">
                                <input type="radio" name="resolution" value="refund" x-model="resolution" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                                <span><span class="block text-sm font-semibold text-slate-900 dark:text-white">Refund customer</span><span class="block text-xs text-slate-500 dark:text-slate-400">Escrow returns to the customer.</span></span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-2 rounded-lg border p-3 transition" :class="resolution === 'dismiss' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/40' : 'border-slate-200 dark:border-slate-700'">
                                <input type="radio" name="resolution" value="dismiss" x-model="resolution" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                                <span><span class="block text-sm font-semibold text-slate-900 dark:text-white">Dismiss</span><span class="block text-xs text-slate-500 dark:text-slate-400">Close without moving money.</span></span>
                            </label>
                        </div>

                        <div>
                            <label for="resolution_note" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Note <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                            <textarea id="resolution_note" name="resolution_note" rows="3"
                                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('resolution_note') }}</textarea>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Submit resolution</button>
                    </form>
                @else
                    <div class="mt-4 rounded-lg bg-slate-50 p-4 text-sm dark:bg-slate-800">
                        <p class="font-semibold text-slate-900 dark:text-white">
                            @if ($dispute->isResolved())
                                Resolved — {{ $dispute->resolution === 'refund' ? 'refunded to customer' : 'released to provider' }}
                            @else
                                Dismissed
                            @endif
                        </p>
                        @if ($dispute->resolution_note)<p class="mt-1 text-slate-600 dark:text-slate-400">{{ $dispute->resolution_note }}</p>@endif
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            {{ $dispute->resolver?->name ? 'by ' . $dispute->resolver->name : '' }}
                            {{ $dispute->resolved_at ? ' · ' . $dispute->resolved_at->format('d M Y') : '' }}
                        </p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</section>
@endsection