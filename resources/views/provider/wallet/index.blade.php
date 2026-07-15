@extends('layouts.provider')

@section('title', 'Wallet — ' . config('app.name'))
@section('page_title', 'Wallet')

@php
    $maxEarn = max(1, (float) $series->max('value'));

    $tabs = [
        'all'       => 'All activity',
        'available' => 'Earnings',
        'escrow'    => 'Escrow',
    ];

    // Ledger entry types → human labels.
    $typeLabels = [
        'hold'                => 'Escrow hold',
        'release_out'         => 'Escrow released',
        'release_in'          => 'Earnings credited',
        'refund_out'          => 'Escrow refunded',
        'withdrawal_hold'     => 'Withdrawal requested',
        'withdrawal_reversed' => 'Withdrawal reversed',
    ];

    $canWithdraw = $profile && $profile->hasPayoutMethod() && (float) $wallet->available_balance >= $minWithdrawal;
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dashboard
        </a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Wallet</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Every rupee, tracked on an append-only ledger.</p>
    </div>

    {{-- ═══ Balances ═══ --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Available (hero) --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-6 shadow-lg lg:col-span-2">
            <div class="pointer-events-none absolute -end-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-16 -start-8 h-40 w-40 rounded-full bg-black/10 blur-2xl"></div>

            <div class="relative">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/15 text-white">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/></svg>
                    </span>
                    <p class="text-sm font-semibold text-brand-100">Available balance</p>
                </div>

                <p class="mt-4 font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    <span class="text-2xl font-bold text-brand-200">Rs.</span> {{ number_format((float) $wallet->available_balance, 0) }}
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-white/15 pt-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-200">This month</p>
                        <p class="mt-0.5 font-display text-lg font-bold text-white">Rs. {{ number_format($earnedThisMonth, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-200">Lifetime earned</p>
                        <p class="mt-0.5 font-display text-lg font-bold text-white">Rs. {{ number_format($totalEarned, 0) }}</p>
                    </div>
                </div>

                <div class="mt-5" x-data="{ open: false, submitting: false }">
                    @if ($canWithdraw)
                        <button type="button" @click="open = true"
                            class="btn-shine inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-brand-800 shadow-sm transition hover:bg-brand-50">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Withdraw funds
                        </button>

                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
                            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900" @click.outside="open = false">
                                <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">Withdraw funds</h3>
                                @php
                                    $payoutMethodLabels = ['bank' => 'Bank transfer', 'jazzcash' => 'JazzCash', 'easypaisa' => 'Easypaisa'];
                                @endphp
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    To {{ $payoutMethodLabels[$profile->payout_method] ?? ucfirst($profile->payout_method) }} · {{ $profile->payout_account_title }} ({{ $profile->payout_account_number }})
                                </p>

                                <form method="POST" action="{{ route('provider.withdrawals.store') }}" @submit="submitting = true" class="mt-4 space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Amount</label>
                                        <div class="relative mt-1.5">
                                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-sm font-semibold text-slate-400">Rs.</span>
                                            <input type="number" name="amount" step="1" min="{{ (int) $minWithdrawal }}" max="{{ (int) $wallet->available_balance }}" required autofocus
                                                value="{{ (int) $wallet->available_balance }}"
                                                class="block w-full rounded-xl border border-slate-200 py-2.5 pe-3.5 ps-11 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                        </div>
                                        <p class="mt-1.5 text-xs text-slate-400">Min. Rs. {{ number_format($minWithdrawal, 0) }} · Max. Rs. {{ number_format((float) $wallet->available_balance, 0) }} available</p>
                                    </div>
                                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-xs leading-relaxed text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        We'll review and send this via {{ $profile->payout_method === 'bank' ? 'bank transfer' : ucfirst($profile->payout_method) }} — usually within 2 business days.
                                    </p>
                                    <div class="flex gap-2 pt-1">
                                        <button type="submit" :disabled="submitting" class="flex-1 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60">
                                            <span x-show="!submitting">Request withdrawal</span>
                                            <span x-show="submitting" x-cloak>Submitting…</span>
                                        </button>
                                        <button type="button" @click="open = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif ($profile && ! $profile->hasPayoutMethod())
                        <a href="#payout-method" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-brand-800 shadow-sm transition hover:bg-brand-50">
                            Add payout details to withdraw
                        </a>
                    @else
                        <p class="text-xs text-brand-200">Minimum withdrawal is Rs. {{ number_format($minWithdrawal, 0) }} — keep earning to unlock it.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Escrow --}}
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-900/60 dark:bg-sky-950/30">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-400">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 118 0v3" stroke-linecap="round"/></svg>
                </span>
                <p class="text-sm font-semibold text-sky-800 dark:text-sky-300">Held in escrow</p>
            </div>

            <p class="mt-4 font-display text-3xl font-extrabold text-sky-900 dark:text-sky-200">Rs. {{ number_format((float) $wallet->escrow_balance, 0) }}</p>
            <p class="mt-2 text-xs leading-relaxed text-sky-700 dark:text-sky-400/90">
                Customer funds waiting on job completion. Released to your available balance once confirmed.
            </p>
        </div>
    </div>

    {{-- ═══ Payout method ═══ --}}
    <section id="payout-method" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ editing: {{ $profile && $profile->hasPayoutMethod() ? 'false' : 'true' }} }">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Payout method</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Where we send your withdrawals.</p>
            </div>
            @if ($profile && $profile->hasPayoutMethod())
                <button type="button" @click="editing = ! editing" class="text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                    <span x-text="editing ? 'Cancel' : 'Edit'"></span>
                </button>
            @endif
        </div>

        @if ($profile && $profile->hasPayoutMethod())
            @php $payoutMethodLabels = ['bank' => 'Bank transfer', 'jazzcash' => 'JazzCash', 'easypaisa' => 'Easypaisa']; @endphp
            <div x-show="!editing" class="mt-4 flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                    <svg viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $payoutMethodLabels[$profile->payout_method] ?? ucfirst($profile->payout_method) }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        {{ $profile->payout_account_title }} · {{ $profile->payout_account_number }}
                        @if ($profile->payout_bank_name) · {{ $profile->payout_bank_name }} @endif
                    </p>
                </div>
            </div>
        @endif

        <form x-show="editing" x-cloak method="POST" action="{{ route('provider.payout-method.update') }}" class="mt-4 space-y-4"
              x-data="{ method: '{{ old('payout_method', $profile->payout_method ?? 'bank') }}' }">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Method</label>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach (['bank' => 'Bank', 'jazzcash' => 'JazzCash', 'easypaisa' => 'Easypaisa'] as $key => $label)
                        <button type="button" @click="method = '{{ $key }}'"
                            :class="method === '{{ $key }}' ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400' : 'border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300'"
                            class="rounded-lg border px-3 py-2 text-xs font-semibold transition">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="payout_method" :value="method">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Account title</label>
                <input type="text" name="payout_account_title" value="{{ old('payout_account_title', $profile->payout_account_title ?? '') }}" required
                    class="mt-1.5 block w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <x-field-error name="payout_account_title" />
            </div>

            <div x-show="method === 'bank'" x-cloak>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Bank name</label>
                <input type="text" name="payout_bank_name" value="{{ old('payout_bank_name', $profile->payout_bank_name ?? '') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <x-field-error name="payout_bank_name" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <span x-text="method === 'bank' ? 'Account / IBAN number' : 'Mobile wallet number'"></span>
                </label>
                <input type="text" name="payout_account_number" value="{{ old('payout_account_number', $profile->payout_account_number ?? '') }}" required
                    class="mt-1.5 block w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <x-field-error name="payout_account_number" />
            </div>

            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Save payout details</button>
        </form>
    </section>

    {{-- ═══ Withdrawal history ═══ --}}
    @if ($withdrawalRequests->isNotEmpty())
        @php
            $wdStatusTones = [
                'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                'paid' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
                'rejected' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
            ];
        @endphp
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Withdrawal requests</h2>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($withdrawalRequests as $wd)
                    <li class="flex items-center justify-between gap-4 px-6 py-3.5">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format($wd->amount, 0) }}</p>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $wdStatusTones[$wd->status] ?? '' }}">{{ $wd->status }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $wd->reference }} · {{ $wd->methodLabel() }}</p>
                            @if ($wd->status === 'rejected' && $wd->admin_notes)
                                <p class="mt-0.5 text-xs text-red-500">{{ $wd->admin_notes }}</p>
                            @endif
                        </div>
                        <time class="shrink-0 text-[11px] text-slate-400">{{ $wd->created_at->format('d M, g:i A') }}</time>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ═══ Earnings chart ═══ --}}
    @if ($totalEarned > 0)
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Earnings trend</h2>
            <p class="mt-0.5 text-xs text-slate-400">Last 6 months, net of commission</p>

            <div class="mt-6 flex h-36 items-end gap-2 sm:gap-4">
                @foreach ($series as $point)
                    @php $pct = (int) round(($point['value'] / $maxEarn) * 100); @endphp
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <p class="text-[10px] font-bold text-slate-400 opacity-0 transition group-hover:opacity-100">
                            {{ $point['value'] > 0 ? number_format($point['value'] / 1000, 1) . 'k' : '0' }}
                        </p>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t-lg bg-gradient-to-t from-brand-600 to-brand-400 transition-all duration-500 ease-out group-hover:from-brand-700 group-hover:to-brand-500"
                                style="height: {{ max($pct, 2) }}%" role="img"
                                aria-label="{{ $point['label'] }}: Rs. {{ number_format($point['value'], 0) }}"></div>
                        </div>
                        <p class="text-[11px] font-semibold text-slate-400">{{ $point['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══ Ledger ═══ --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Activity</h2>

            <nav class="flex gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800" aria-label="Filter ledger">
                @foreach ($tabs as $key => $label)
                    @php $active = $bucket === $key; @endphp
                    <a href="{{ route('provider.wallet.index', $key === 'all' ? [] : ['bucket' => $key]) }}"
                       @if ($active) aria-current="page" @endif
                       class="rounded-lg px-3 py-1.5 text-xs font-semibold transition
                           {{ $active ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ $label }}
                        @if (($counts[$key] ?? 0) > 0)
                            <span class="ms-1 text-slate-400">{{ $counts[$key] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($entries->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/></svg>
                </span>
                <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">
                    {{ $bucket === 'all' ? 'No wallet activity yet' : 'Nothing in this view' }}
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Complete a paid booking and your earnings will land here.</p>
            </div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($entries as $entry)
                    @php
                        $amount   = (float) $entry->amount;
                        $isCredit = $amount > 0;
                        $isEscrow = $entry->bucket === 'escrow';
                        $booking  = $entry->payment?->booking;
                    @endphp

                    <li class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                        {{-- Icon --}}
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                            @if ($isEscrow) bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400
                            @elseif ($isCredit) bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400
                            @else bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 @endif">
                            @if ($isEscrow)
                                <svg viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 118 0v3" stroke-linecap="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 {{ $isCredit ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </span>

                        {{-- Body --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $typeLabels[$entry->type] ?? ucfirst(str_replace('_', ' ', $entry->type)) }}
                                </p>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide
                                    {{ $isEscrow
                                        ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400'
                                        : 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400' }}">
                                    {{ $isEscrow ? 'Escrow' : 'Available' }}
                                </span>
                            </div>
                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $entry->description }}</p>
                            @if ($booking)
                                <a href="{{ route('provider.bookings.show', $booking) }}"
                                   class="mt-1 inline-flex items-center gap-1 font-mono text-[11px] text-slate-400 transition hover:text-brand-600">
                                    {{ $booking->reference }}
                                    <svg viewBox="0 0 24 24" class="h-3 w-3 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            @endif
                        </div>

                        {{-- Amount --}}
                        <div class="shrink-0 text-end">
                            <p class="font-display text-sm font-extrabold tabular-nums
                                {{ $isCredit ? 'text-brand-700 dark:text-brand-400' : 'text-slate-400' }}">
                                {{ $isCredit ? '+' : '−' }} Rs. {{ number_format(abs($amount), 0) }}
                            </p>
                            <time class="text-[11px] text-slate-400" datetime="{{ $entry->created_at->toIso8601String() }}">
                                {{ $entry->created_at->format('d M, g:i A') }}
                            </time>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($entries->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">{{ $entries->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection