@extends('layouts.admin')

@section('title', $withdrawal->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('admin.withdrawals.index') }}" />
    </div>

    @php
        $statusTones = [
            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
            'awaiting_confirmation' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400',
            'paid' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
            'rejected' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
        ];
        $statusLabels = [
            'pending' => 'Pending',
            'awaiting_confirmation' => 'Awaiting provider confirmation',
            'paid' => 'Paid',
            'rejected' => 'Rejected',
        ];
    @endphp

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Rs. {{ number_format($withdrawal->amount, 0) }}</h1>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTones[$withdrawal->status] ?? '' }}">{{ $statusLabels[$withdrawal->status] ?? ucfirst($withdrawal->status) }}</span>
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $withdrawal->reference }} · requested {{ $withdrawal->created_at->format('d M Y, g:i A') }}</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->providerProfile->business_name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Contact</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->providerProfile->user->email }} · {{ $withdrawal->providerProfile->user->phone }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Method</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->methodLabel() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Account title</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->payout_account_title }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Account / number</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->payout_account_number }}</dd></div>
            @if ($withdrawal->payout_bank_name)
                <div><dt class="text-slate-500 dark:text-slate-400">Bank</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->payout_bank_name }}</dd></div>
            @endif
            @if ($withdrawal->processor)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Processed by</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->processor->name }} · {{ $withdrawal->processed_at?->format('d M Y, g:i A') }}</dd></div>
            @endif
            @if ($withdrawal->provider_confirmed_at)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Provider confirmed</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->provider_confirmed_at->format('d M Y, g:i A') }}</dd></div>
            @endif
            @if ($withdrawal->admin_notes)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $withdrawal->admin_notes }}</dd></div>
            @endif
        </dl>
    </div>

    @if ($withdrawal->screenshot_path)
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Transfer screenshot</h2>
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($withdrawal->screenshot_path) }}" target="_blank" rel="noopener" class="mt-3 block overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($withdrawal->screenshot_path) }}" alt="Transfer screenshot" class="w-full">
            </a>
        </div>
    @endif

    @if ($withdrawal->isPending())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Process this request</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Send Rs. {{ number_format($withdrawal->amount, 0) }} via {{ $withdrawal->methodLabel() }} to the account above.</p>

            <div class="mt-4 space-y-4">
                <x-confirm-form :action="route('admin.withdrawals.paid-cash', $withdrawal)" method="POST"
                    button-label="Paid in cash (in-person)" button-class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                    title="Mark this withdrawal as paid in cash?" message="Confirm only after you've actually handed over the cash." confirm-label="Mark paid" confirm-class="bg-brand-600 hover:bg-brand-700" />

                <div x-data="{ open: false }">
                    <button type="button" @click="open = ! open" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Sent via bank transfer</button>

                    <form x-show="open" x-cloak method="POST" action="{{ route('admin.withdrawals.paid-bank-transfer', $withdrawal) }}" enctype="multipart/form-data"
                          class="mt-3 space-y-3" x-data="{...fileDrop(), submitting: false}" @submit="submitting = true">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Attach proof of transfer</label>
                        <div class="flex items-center gap-2">
                            <x-file-drop name="screenshot" />
                        </div>
                        <x-field-error name="screenshot" />
                        <button type="submit" :disabled="submitting" class="rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="! submitting">Submit &amp; await provider confirmation</span>
                            <span x-show="submitting" x-cloak>Submitting…</span>
                        </button>
                    </form>
                </div>

                <div x-data="{ rejecting: false }">
                    <button type="button" @click="rejecting = ! rejecting" class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">Reject</button>

                    <form x-show="rejecting" x-cloak method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="admin_notes" rows="2" placeholder="Reason (optional) — shown to the provider"
                            class="block w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700">Confirm rejection</button>
                    </form>
                </div>
            </div>
        </div>
    @elseif ($withdrawal->isAwaitingConfirmation())
        <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-900/60 dark:bg-sky-950/30">
            <p class="text-sm font-semibold text-sky-900 dark:text-sky-300">Waiting on the provider to confirm they received this transfer.</p>
            <p class="mt-1 text-xs text-sky-800 dark:text-sky-400/90">This will move to "Paid" automatically once they confirm — nothing else to do here.</p>
        </div>
    @endif
</section>
@endsection
