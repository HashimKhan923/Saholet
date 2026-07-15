@extends('layouts.app')

@section('title', 'Refer & earn — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8" x-data="{ copied: false, copy() { navigator.clipboard.writeText('{{ $referralUrl }}'); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
    <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Refer & earn</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Share your link — when a friend signs up and makes their first payment, you both get a credit.</p>

    <div class="mt-6 grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Your credit balance</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-brand-700 dark:text-brand-400">PKR {{ number_format($user->credit_balance, 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Friends referred</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-slate-900 dark:text-white">{{ $referredUsers->count() }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Your referral link</h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Referral code: <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $user->referral_code }}</span></p>
        <div class="mt-3 flex items-center gap-2">
            <input readonly type="text" value="{{ $referralUrl }}"
                class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
            <button type="button" @click="copy()"
                class="shrink-0 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
            </button>
        </div>
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
            You earn <strong>PKR {{ number_format(config('referrals.referrer_reward'), 0) }}</strong> and they earn
            <strong>PKR {{ number_format(config('referrals.referred_reward'), 0) }}</strong> credit once they complete their first payment on Sahoulet.
        </p>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">People you've referred</h2>
        <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($referredUsers as $referred)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $referred->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Joined {{ $referred->created_at->format('M j, Y') }}</p>
                    </div>
                    @if ($rewards->has($referred->id))
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                            +PKR {{ number_format($rewards[$referred->id]->referrer_reward, 0) }} earned
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            Awaiting first payment
                        </span>
                    @endif
                </div>
            @empty
                <p class="py-6 text-center text-sm text-slate-400">No referrals yet — share your link above to get started.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
