@extends('layouts.admin')

@section('title', 'Admin dashboard — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <span class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white dark:bg-slate-100 dark:text-slate-900">{{ __('messages.admin_dashboard.badge') }}</span>
        <h1 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.welcome', ['name' => auth()->user()->name]) }}</h1>
        <p class="mt-2 max-w-prose text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.admin_dashboard.subtitle') }}</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.approved_providers') }}</p>
                <p class="mt-1 font-display text-xl font-extrabold text-slate-900 dark:text-white">{{ $metrics['providers_approved'] }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.total_bookings') }}</p>
                <p class="mt-1 font-display text-xl font-extrabold text-slate-900 dark:text-white">{{ $metrics['bookings_total'] }}</p>
            </div>
            <div class="rounded-xl bg-brand-50 p-4 dark:bg-brand-950/40">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-400">{{ __('messages.admin_dashboard.commission_earned') }}</p>
                <p class="mt-1 font-display text-xl font-extrabold text-brand-900 dark:text-brand-300">Rs. {{ number_format($metrics['commission_earned'], 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Operations --}}
    <h2 class="mt-10 font-display text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.operations_section') }}</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.categories.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><x-service-icon name="default" class="h-6 w-6" /></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.categories_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.categories_desc') }}</p>
        </a>
        <a href="{{ route('admin.services.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><x-service-icon name="appliance" class="h-6 w-6" /></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.services_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.services_desc') }}</p>
        </a>
        <a href="{{ route('admin.providers.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg></span>
                @if ($pendingProviders > 0)<span class="inline-flex items-center rounded-full bg-amber-500 px-2.5 py-1 text-xs font-bold text-white">{{ $pendingProviders }}</span>@endif
            </div>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.provider_approvals_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.provider_approvals_desc') }}</p>
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4M12 16.5v.5" stroke-linecap="round"/><path d="M10.3 3.3 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.3a2 2 0 0 0-3.4 0z" stroke-linejoin="round"/></svg></span>
                @if ($openDisputes > 0)<span class="inline-flex items-center rounded-full bg-red-500 px-2.5 py-1 text-xs font-bold text-white">{{ $openDisputes }}</span>@endif
            </div>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.disputes_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.disputes_desc') }}</p>
        </a>
    </div>

    {{-- Tools --}}
    <h2 class="mt-10 font-display text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.tools_section') }}</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <a href="{{ route('admin.analytics.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.analytics_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.analytics_desc') }}</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5M16 11h5M18.5 8.5v5" stroke-linecap="round"/></svg></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.users_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.users_desc') }}</p>
        </a>
        <a href="{{ route('admin.service-areas.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.service_areas_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.service_areas_desc') }}</p>
        </a>
        <a href="{{ route('admin.fraud.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M9.5 12 11 13.5 14.5 10" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.fraud_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.fraud_desc') }}</p>
        </a>
        <a href="{{ route('admin.settings.edit') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950/50 dark:text-brand-400"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.4-2.3 1a7 7 0 0 0-2-1.2L14 2h-4l-.6 2.7a7 7 0 0 0-2 1.2l-2.3-1-2 3.4 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.4 2.3-1a7 7 0 0 0 2 1.2L10 22h4l.6-2.7a7 7 0 0 0 2-1.2l2.3 1 2-3.4-2-1.5c.1-.4.1-.8.1-1.2z" stroke-linejoin="round"/></svg></span>
            <h3 class="mt-4 font-display text-base font-bold text-slate-900 dark:text-white">{{ __('messages.admin_dashboard.settings_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.admin_dashboard.settings_desc') }}</p>
        </a>
    </div>
</section>
@endsection