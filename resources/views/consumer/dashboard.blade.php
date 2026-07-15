@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">
            {{ __('messages.consumer_dashboard.badge') }}
        </span>
        <h1 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
            {{ __('messages.consumer_dashboard.welcome', ['name' => auth()->user()->name]) }}
        </h1>
        <p class="mt-2 max-w-prose text-sm leading-relaxed text-slate-600 dark:text-slate-400">
            {{ __('messages.consumer_dashboard.subtitle') }}
        </p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('services.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.consumer_dashboard.browse_services') }}</a>
            <a href="{{ route('consumer.jobs.create') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('messages.consumer_dashboard.post_job') }}</a>
            <a href="{{ route('consumer.contracts.create') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('messages.consumer_dashboard.request_contract') }}</a>
            <a href="{{ route('consumer.emergencies.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M12 8v4M12 15.5v.2" stroke-linecap="round"/></svg>
                {{ __('messages.consumer_dashboard.emergency_help') }}
            </a>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('consumer.bookings.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.consumer_dashboard.my_bookings') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.consumer_dashboard.my_bookings_desc') }}</p>
        </a>

        <a href="{{ route('consumer.jobs.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.consumer_dashboard.my_jobs') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.consumer_dashboard.my_jobs_desc') }}</p>
        </a>

        <a href="{{ route('consumer.contracts.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 4h6l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke-linejoin="round"/><path d="M9 12h6M9 16h6" stroke-linecap="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.consumer_dashboard.my_contracts') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.consumer_dashboard.my_contracts_desc') }}</p>
        </a>

        <a href="{{ route('consumer.emergencies.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-red-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 transition group-hover:bg-red-600 group-hover:text-white dark:bg-red-950/40 dark:text-red-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M12 8v4M12 15.5v.2" stroke-linecap="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.consumer_dashboard.emergencies') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.consumer_dashboard.emergencies_desc') }}</p>
        </a>

        <a href="{{ route('consumer.addresses.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="10" r="3"/><path d="M12 2c4.4 0 8 3.6 8 8 0 5-8 12-8 12S4 15 4 10c0-4.4 3.6-8 8-8z" stroke-linejoin="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">My addresses</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Save places for one-click checkout.</p>
        </a>

        <a href="{{ route('consumer.referrals.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4"/><path d="M23 20v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">Refer & earn</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Invite friends, earn credit — PKR {{ number_format(config('referrals.referrer_reward'), 0) }} per referral.</p>
        </a>

        <a href="{{ route('consumer.subscriptions.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M17 2.1l4 4-4 4M7 21.9l-4-4 4-4" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.5 12a8.5 8.5 0 0 1 14.5-6h-4M20.5 12a8.5 8.5 0 0 1-14.5 6h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">My subscriptions</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Recurring maintenance plans — never miss a service again.</p>
        </a>

        <a href="{{ auth()->user()->corporate_account_id ? route('consumer.corporate.show') : route('consumer.corporate.create') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="10" height="18" rx="1"/><path d="M14 8h6v13h-6M7 7h.01M10 7h.01M7 11h.01M10 11h.01M7 15h.01M10 15h.01" stroke-linecap="round"/></svg>
            </span>
            <h2 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ auth()->user()->corporate_account_id ? 'Company dashboard' : 'Set up a company account' }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add teammates and consolidate billing across your whole company.</p>
        </a>
    </div>

    {{-- Recent bookings --}}
    <div class="mt-8">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.consumer_dashboard.recent_bookings') }}</h2>
            <a href="{{ route('consumer.bookings.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300">{{ __('messages.consumer_dashboard.view_all') }}</a>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($bookings as $booking)
                <a href="{{ route('consumer.bookings.show', $booking) }}"
                   class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->service->name }}</span>
                                <x-booking-status :status="$booking->status" />
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }}
                                · {{ $booking->dateLabel() }} at {{ $booking->timeLabel() }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($booking->price, 0) }}</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('messages.consumer_dashboard.empty_title') }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('messages.consumer_dashboard.empty_desc') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection