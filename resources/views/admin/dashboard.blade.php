@extends('layouts.admin')

@php
    $u = auth()->user();
    $showOperationsSection = $u->hasPermission('bookings') || $u->hasPermission('categories') || $u->hasPermission('services')
        || $u->hasPermission('providers') || $u->hasPermission('disputes');
    $showToolsSection = $u->isAdmin() || $u->hasPermission('service-areas') || $u->hasPermission('fraud');
@endphp

@section('title', ($u->isStaff() ? 'Staff dashboard' : 'Admin dashboard') . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-white to-brand-200 shadow-lg dark:from-slate-900 dark:to-brand-900">
        <div class="absolute inset-0 bg-dot-grid opacity-40"></div>

        <div class="relative flex flex-col gap-8 p-8 sm:p-10 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/></svg>
                    {{ $u->isStaff() ? __('admin.dashboard.badge_staff') : __('admin.dashboard.badge') }}
                </span>
                <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('admin.dashboard.welcome', ['name' => $u->name]) }}</h1>
                <p class="mt-3 max-w-prose text-base leading-relaxed text-slate-600 dark:text-slate-400">{{ __('admin.dashboard.subtitle') }}</p>
            </div>

            {{-- Decorative composition — not a literal illustration, just an abstract echo of "dashboard + people + trust" in SVG so it stays crisp and theme-consistent at any size. --}}
            <div class="hidden shrink-0 items-end gap-3 lg:flex" aria-hidden="true">
                <span class="-mb-2 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-md ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5M16 11h5M18.5 8.5v5" stroke-linecap="round"/></svg>
                </span>
                <span class="flex h-32 w-44 flex-col justify-between rounded-2xl bg-white p-3 shadow-xl ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                    <span class="flex items-end gap-1 h-14">
                        <span class="w-2.5 rounded-sm bg-brand-200" style="height:40%"></span>
                        <span class="w-2.5 rounded-sm bg-brand-300" style="height:65%"></span>
                        <span class="w-2.5 rounded-sm bg-brand-500" style="height:50%"></span>
                        <span class="w-2.5 rounded-sm bg-brand-600" style="height:90%"></span>
                        <span class="w-2.5 rounded-sm bg-brand-700" style="height:75%"></span>
                    </span>
                    <span class="h-1.5 w-full rounded-full bg-slate-100"></span>
                    <span class="h-1.5 w-2/3 rounded-full bg-slate-100"></span>
                </span>
                <span class="-mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-xl ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                    <svg viewBox="0 0 24 24" class="h-8 w-8 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </div>

        @if ($u->isAdmin())
        <div class="relative border-t border-brand-100 bg-white px-6 py-6 dark:border-slate-800 dark:bg-slate-900 sm:px-10">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-dashboard-stat :label="__('admin.dashboard.approved_providers')" :value="$metrics['providers_approved']" :href="route('admin.providers.index')" :badge="$pendingProviders ?: null">
                    <svg viewBox="0 0 24 24" class="h-full w-full" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5M16 11h5M18.5 8.5v5" stroke-linecap="round"/></svg>
                </x-dashboard-stat>
                <x-dashboard-stat :label="__('admin.dashboard.total_bookings')" :value="$metrics['bookings_total']" :href="route('admin.bookings.index')">
                    <svg viewBox="0 0 24 24" class="h-full w-full" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 11h16" stroke-linecap="round"/></svg>
                </x-dashboard-stat>
                <x-dashboard-stat :label="__('admin.dashboard.total_orders')" :value="$metrics['orders_total']" :href="route('admin.orders.index')">
                    <svg viewBox="0 0 24 24" class="h-full w-full" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 7l-8-4-8 4m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </x-dashboard-stat>
                <x-dashboard-stat :label="__('admin.dashboard.commission_earned')" value="Rs. {{ number_format($metrics['commission_earned'], 0) }}" :href="route('admin.analytics.index')">
                    <svg viewBox="0 0 24 24" class="h-full w-full" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </x-dashboard-stat>
            </div>
        </div>
        @endif
    </div>

    @if ($showOperationsSection)
    {{-- Operations --}}
    <h2 class="mt-10 font-display text-base font-bold uppercase tracking-wide text-slate-900 dark:text-slate-100">{{ __('admin.dashboard.operations_section') }}</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @if ($u->hasPermission('bookings'))
        <a href="{{ route('admin.bookings.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 11h16" stroke-linecap="round"/></svg></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">Bookings</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Every booking across direct, bids, contracts, subscriptions & emergencies.</p>
        </a>
        @endif
        @if ($u->hasPermission('categories'))
        <a href="{{ route('admin.categories.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><x-service-icon name="default" class="h-6 w-6" /></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.categories_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.categories_desc') }}</p>
        </a>
        @endif
        @if ($u->hasPermission('services'))
        <a href="{{ route('admin.services.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><x-service-icon name="appliance" class="h-6 w-6" /></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.services_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.services_desc') }}</p>
        </a>
        @endif
        @if ($u->hasPermission('providers'))
        <a href="{{ route('admin.providers.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg></span>
                @if ($pendingProviders > 0)<span class="inline-flex items-center rounded-full bg-amber-500 px-2.5 py-1 text-xs font-bold text-white">{{ $pendingProviders }}</span>@endif
            </div>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.provider_approvals_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.provider_approvals_desc') }}</p>
        </a>
        @endif
        @if ($u->hasPermission('disputes'))
        <a href="{{ route('admin.disputes.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4M12 16.5v.5" stroke-linecap="round"/><path d="M10.3 3.3 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.3a2 2 0 0 0-3.4 0z" stroke-linejoin="round"/></svg></span>
                @if ($openDisputes > 0)<span class="inline-flex items-center rounded-full bg-red-500 px-2.5 py-1 text-xs font-bold text-white">{{ $openDisputes }}</span>@endif
            </div>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.disputes_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.disputes_desc') }}</p>
        </a>
        @endif
    </div>
    @endif

    @if ($showToolsSection)
    {{-- Tools --}}
    <h2 class="mt-10 font-display text-base font-bold uppercase tracking-wide text-slate-900 dark:text-slate-100">{{ __('admin.dashboard.tools_section') }}</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @if ($u->isAdmin())
        <a href="{{ route('admin.analytics.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.analytics_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.analytics_desc') }}</p>
        </a>
        @endif
        @if ($u->isAdmin())
        <a href="{{ route('admin.users.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5M16 11h5M18.5 8.5v5" stroke-linecap="round"/></svg></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.users_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.users_desc') }}</p>
        </a>
        @endif
        @if ($u->hasPermission('service-areas'))
        <a href="{{ route('admin.service-areas.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.service_areas_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.service_areas_desc') }}</p>
        </a>
        @endif
        @if ($u->hasPermission('fraud'))
        <a href="{{ route('admin.fraud.index') }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-brand-50/60 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:from-slate-900 dark:to-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white transition group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-brand-500 dark:group-hover:bg-brand-950/50 dark:group-hover:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M9.5 12 11 13.5 14.5 10" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('admin.dashboard.fraud_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.fraud_desc') }}</p>
        </a>
        @endif
    </div>
    @endif
</section>
@endsection