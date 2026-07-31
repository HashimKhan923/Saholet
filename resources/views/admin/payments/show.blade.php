@extends('layouts.admin')

@section('title', 'Review payment — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('admin.payments.index') }}" />
    </div>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $payment->reference }}</h1>
        @switch($payment->status)
            @case('pending')
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Pending verification</span>
                @break
            @case('escrow')
            @case('released')
                <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Verified</span>
                @break
            @case('failed')
                <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">Rejected</span>
                @break
        @endswitch
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Details</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Booking</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $payment->booking->reference ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Service</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $payment->booking->service->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Customer</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $payment->consumer->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $payment->booking->providerProfile->business_name ?? $payment->booking->providerProfile->user->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Amount</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $payment->amount, 0) }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Submitted</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $payment->created_at->format('d M Y, h:i A') }}</dd></div>
                    @if ($payment->verified_at)
                        <div><dt class="text-slate-500 dark:text-slate-400">Verified</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $payment->verified_at->format('d M Y, h:i A') }} by {{ $payment->verifier->name ?? '—' }}</dd></div>
                    @endif
                    @if ($payment->notes)
                        <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="font-medium text-red-700 dark:text-red-400">{{ $payment->notes }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Payment screenshot</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Cross-check this against Sahoulat's real bank/JazzCash statement before verifying.</p>
                @if ($payment->screenshot_path)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->screenshot_path) }}" target="_blank" rel="noopener" class="mt-4 block overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->screenshot_path) }}" alt="Payment screenshot" class="w-full">
                    </a>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No screenshot attached.</p>
                @endif
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Decision</h2>

                @if ($payment->isPending())
                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Verify &amp; release to provider</button>
                    </form>

                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reason for rejection</label>
                        <textarea id="notes" name="notes" rows="3" required
                            class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes') }}</textarea>
                        <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">Reject</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        @if ($payment->status === 'failed')
                            Rejected. The customer has been asked to resubmit.
                        @else
                            Verified and released to the provider's wallet.
                        @endif
                    </p>
                @endif
            </div>
        </aside>
    </div>
</section>
@endsection
