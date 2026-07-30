@extends('layouts.provider')

@section('title', 'Settle commission — ' . config('app.name'))
@section('page_title', 'Settle commission')

@php
    $owed = (float) $wallet->available_balance < 0 ? abs((float) $wallet->available_balance) : 0.0;
    $statusTones = [
        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
        'confirmed' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
        'rejected' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('provider.wallet.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Wallet
        </a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Settle commission</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">When a customer pays you in cash, our commission on that job is deducted from your wallet. Pay it back here to clear the balance.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Outstanding balance</p>
        <p class="mt-1.5 font-display text-2xl font-extrabold {{ $owed > 0 ? 'text-red-600 dark:text-red-400' : 'text-brand-700 dark:text-brand-400' }}">
            {{ $owed > 0 ? 'Rs. ' . number_format($owed, 0) . ' owed' : 'All settled up' }}
        </p>
        @if ($owed > 0)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Unpaid balances older than {{ config('payments.settlement_grace_days') }} days will automatically pause new bookings until settled.</p>
        @endif
    </div>

    @if ($owed > 0)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Submit a settlement</h2>

            <form method="POST" action="{{ route('provider.settlements.store') }}" enctype="multipart/form-data" class="mt-4 space-y-5"
                  x-data="{...fileDrop(), method: '{{ old('method', 'cash') }}', submitting: false}" @submit="submitting = true">
                @csrf

                <div class="space-y-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                           :class="method === 'cash' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                        <input type="radio" name="method" value="cash" x-model="method" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white">Cash, in person</span>
                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Bring it to the office — we'll confirm once received.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                           :class="method === 'bank_transfer' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                        <input type="radio" name="method" value="bank_transfer" x-model="method" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white">Bank transfer</span>
                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Transfer to Sahoulat's account and attach a screenshot.</span>
                        </span>
                    </label>
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Amount (Rs.)</label>
                    <input id="amount" name="amount" type="number" step="1" min="1" max="{{ (int) $owed }}" required
                        value="{{ old('amount', (int) $owed) }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <p class="mt-1.5 text-xs text-slate-400">Paying less than the full amount is fine — the rest stays outstanding.</p>
                    <x-field-error name="amount" />
                </div>

                <div x-show="method === 'bank_transfer'" x-cloak>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Payment screenshot</label>
                    <div class="mt-2 flex items-center gap-2">
                        <x-file-drop name="screenshot" />
                    </div>
                    <x-field-error name="screenshot" />
                </div>

                <button type="submit" :disabled="submitting"
                    class="w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                    <span x-show="! submitting">Submit settlement</span>
                    <span x-show="submitting" x-cloak>Submitting…</span>
                </button>
            </form>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">History</h2>
        @if ($history->isEmpty())
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No settlements submitted yet.</p>
        @else
            <ul class="mt-4 space-y-3">
                @foreach ($history as $s)
                    <li class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-800">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $s->amount, 0) }} · {{ $s->methodLabel() }}</p>
                            <p class="text-xs text-slate-400">{{ $s->created_at->format('d M Y') }} · {{ $s->reference }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTones[$s->status] ?? '' }}">{{ ucfirst($s->status) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $history->links() }}</div>
        @endif
    </div>
</div>
@endsection
