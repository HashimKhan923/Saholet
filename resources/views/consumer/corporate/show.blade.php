@extends('layouts.app')

@section('title', $account->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $account->name }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Owner: {{ $account->owner->name }} · {{ $account->billing_email }}</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="mt-6 grid grid-cols-2 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Team members</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-slate-900 dark:text-white">{{ $account->members->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Consolidated spend</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format($totalSpend, 0) }}</p>
        </div>
    </div>

    @if (auth()->user()->isCorporateOwner())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Add a teammate</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">They must already have a sahoulat consumer account with this email.</p>
            <form method="POST" action="{{ route('consumer.corporate.members.invite') }}" class="mt-3 flex gap-2">
                @csrf
                <input type="email" name="email" required placeholder="teammate@company.com"
                    class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <button type="submit" class="shrink-0 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Add</button>
            </form>
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Team</h2>
        <div class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($account->members as $member)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $member->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $member->email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($member->corporate_role === 'owner')
                            <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Owner</span>
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Member</span>
                            @if (auth()->user()->isCorporateOwner())
                                <x-confirm-form :action="route('consumer.corporate.members.remove', $member)" method="DELETE"
                                    button-label="Remove" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Remove this teammate?" confirm-label="Remove" />
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
