@extends('layouts.portal')

@section('portal_label', __('provider.portal_label'))

@section('nav')
    <x-portal-nav-link :href="route('provider.dashboard')" :label="__('provider.nav.dashboard')" :active="request()->routeIs('provider.dashboard')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="12" width="8" height="9" rx="1.5"/><rect x="3" y="14" width="8" height="7" rx="1.5"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('notifications.index')" :label="__('provider.nav.notifications')" :active="request()->routeIs('notifications.*')" :badge="$sidebarUnreadNotifications ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 9a6 6 0 1 1 12 0c0 5 2 6 2 6H4s2-1 2-6z" stroke-linejoin="round"/><path d="M10 20a2 2 0 0 0 4 0" stroke-linecap="round"/></svg>
    </x-portal-nav-link>

    <p class="mt-5 px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-900 dark:text-slate-100">{{ __('provider.nav.work_section') }}</p>
    <x-portal-nav-link :href="route('provider.bookings.index')" :label="__('provider.nav.bookings')" :active="request()->routeIs('provider.bookings.*')" :badge="$sidebarPendingBookings ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <a href="{{ route('provider.jobs.index') }}"
        x-data="{
            count: {{ (int) $sidebarAvailableJobs }},
            myServiceIds: @js($sidebarMyServiceIds ?? []),
            init() {
                if (! window.Echo) return;
                window.Echo.channel('jobs')
                    .listen('.job.created', (e) => { if (this.myServiceIds.includes(e.service_id)) this.count++; })
                    .listen('.job.status.updated', () => { if (this.count > 0) this.count--; });
            },
        }"
        class="group flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition
            {{ request()->routeIs('provider.jobs.*')
                ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-400'
                : 'text-slate-600 hover:translate-x-0.5 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
        <span class="flex items-center gap-3">
            <span class="h-5 w-5 {{ request()->routeIs('provider.jobs.*') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/></svg>
            </span>
            {{ __('provider.nav.available_jobs') }}
        </span>
        <span x-show="count > 0" x-cloak x-text="count" class="inline-flex items-center rounded-full bg-red-500 px-2 py-0.5 text-[11px] font-bold text-white"></span>
    </a>
    <x-portal-nav-link :href="route('provider.bids.index')" :label="__('provider.nav.my_bids')" :active="request()->routeIs('provider.bids.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v18M7 8l5-5 5 5M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>

    <p class="mt-5 px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-900 dark:text-slate-100">{{ __('provider.nav.business_section') }}</p>
    <x-portal-nav-link :href="route('provider.services.index')" :label="__('provider.nav.my_services')" :active="request()->routeIs('provider.services.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="16" height="18" rx="2"/><line x1="4" y1="9" x2="20" y2="9" stroke-linecap="round"/><circle cx="8" cy="6" r="0.6" fill="currentColor"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('provider.portfolio.index')" :label="__('provider.nav.portfolio')" :active="request()->routeIs('provider.portfolio.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m3 15 4.5-4.5a2 2 0 0 1 2.8 0L15 15M13.5 13.5 15.5 11.5a2 2 0 0 1 2.8 0L21 14.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('provider.wallet.index')" :label="__('provider.nav.wallet')" :active="request()->routeIs('provider.wallet.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('provider.onboarding')" :label="__('provider.nav.verification')" :active="request()->routeIs('provider.onboarding')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 5 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M9 12.5l2 2 4-4.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
@endsection
