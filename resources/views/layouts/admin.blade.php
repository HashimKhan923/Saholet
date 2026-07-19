@extends('layouts.portal')

@section('portal_label', 'Admin')

@section('nav')
    <x-portal-nav-link :href="route('admin.dashboard')" :label="__('messages.admin_nav.dashboard')" :active="request()->routeIs('admin.dashboard')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="12" width="8" height="9" rx="1.5"/><rect x="3" y="14" width="8" height="7" rx="1.5"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.requests.index')" :label="__('messages.admin_nav.requests')" :active="request()->routeIs('admin.requests.*')" :badge="$sidebarTotalRequests ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 5h14v11H8l-3 3V5Z" stroke-linejoin="round"/><path d="M9 9h6M9 12h4" stroke-linecap="round"/></svg>
    </x-portal-nav-link>

    <p class="mt-5 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ __('messages.admin_nav.catalog_section') }}</p>
    <x-portal-nav-link :href="route('admin.categories.index')" :label="__('messages.admin_nav.categories')" :active="request()->routeIs('admin.categories.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.services.index')" :label="__('messages.admin_nav.services')" :active="request()->routeIs('admin.services.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="16" height="18" rx="2"/><line x1="4" y1="9" x2="20" y2="9" stroke-linecap="round"/><circle cx="8" cy="6" r="0.6" fill="currentColor"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.service-areas.index')" :label="__('messages.admin_nav.service_areas')" :active="request()->routeIs('admin.service-areas.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>

    <p class="mt-5 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ __('messages.admin_nav.content_section') }}</p>
    <x-portal-nav-link :href="route('admin.faqs.index')" :label="__('messages.admin_nav.faqs')" :active="request()->routeIs('admin.faqs.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.2a2.5 2.5 0 0 1 4.8 1c0 1.5-2.3 1.8-2.3 3.3" stroke-linecap="round"/><circle cx="12" cy="17" r="0.6" fill="currentColor" stroke="none"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.contact-messages.index')" :label="__('messages.admin_nav.contact_messages')" :active="request()->routeIs('admin.contact-messages.*')" :badge="$sidebarUnreadContactMessages ?? null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 6.5 8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>

    <p class="mt-5 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ __('messages.admin_nav.operations_section') }}</p>
    <x-portal-nav-link :href="route('admin.contracts.index')" :label="__('messages.admin_nav.contracts')" :active="request()->routeIs('admin.contracts.*')" :badge="$sidebarPendingContracts ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 4h6l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke-linejoin="round"/><path d="M9 12h6M9 16h6" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.subscriptions.index')" :label="__('messages.admin_nav.subscriptions')" :active="request()->routeIs('admin.subscriptions.*') || request()->routeIs('admin.subscription-plans.*')" :badge="$sidebarPendingSubscriptions ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M17 2.1l4 4-4 4M7 21.9l-4-4 4-4" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.5 12a8.5 8.5 0 0 1 14.5-6h-4M20.5 12a8.5 8.5 0 0 1-14.5 6h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.corporate-accounts.index')" :label="__('messages.admin_nav.corporate_accounts')" :active="request()->routeIs('admin.corporate-accounts.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="10" height="18" rx="1"/><path d="M14 8h6v13h-6M7 7h.01M10 7h.01M7 11h.01M10 11h.01M7 15h.01M10 15h.01" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.providers.index')" :label="__('messages.admin_nav.providers')" :active="request()->routeIs('admin.providers.*')" :badge="$sidebarPendingProviders ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.careers.index')" :label="__('messages.admin_nav.careers')" :active="request()->routeIs('admin.careers.*') || request()->routeIs('admin.career-categories.*')" :badge="$sidebarNewApplications ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 12h18" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.talent.index')" :label="__('messages.admin_nav.talent_search')" :active="request()->routeIs('admin.talent.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.disputes.index')" :label="__('messages.admin_nav.disputes')" :active="request()->routeIs('admin.disputes.*')" :badge="$sidebarOpenDisputes ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4M12 16.5v.5" stroke-linecap="round"/><path d="M10.3 3.3 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.3a2 2 0 0 0-3.4 0z" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.withdrawals.index')" label="Withdrawals" :active="request()->routeIs('admin.withdrawals.*')" :badge="$sidebarPendingWithdrawals ?: null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/><path d="M8 16.5l2-2 2 1.5 2-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.fraud.index')" :label="__('messages.admin_nav.fraud_signals')" :active="request()->routeIs('admin.fraud.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M9.5 12 11 13.5 14.5 10" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.users.index')" :label="__('messages.admin_nav.users')" :active="request()->routeIs('admin.users.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5M16 11h5M18.5 8.5v5" stroke-linecap="round"/></svg>
    </x-portal-nav-link>

    <p class="mt-5 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ __('messages.admin_nav.insights_section') }}</p>
    <x-portal-nav-link :href="route('admin.analytics.index')" :label="__('messages.admin_nav.analytics')" :active="request()->routeIs('admin.analytics.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
    <x-portal-nav-link :href="route('admin.settings.edit')" :label="__('messages.admin_nav.settings')" :active="request()->routeIs('admin.settings.*')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.4-2.3 1a7 7 0 0 0-2-1.2L14 2h-4l-.6 2.7a7 7 0 0 0-2 1.2l-2.3-1-2 3.4 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.4 2.3-1a7 7 0 0 0 2 1.2L10 22h4l.6-2.7a7 7 0 0 0 2-1.2l2.3 1 2-3.4-2-1.5c.1-.4.1-.8.1-1.2z" stroke-linejoin="round"/></svg>
    </x-portal-nav-link>
@endsection
