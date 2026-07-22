@extends('layouts.admin')

@section('title', 'Requests inbox — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('messages.admin_requests.title') }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            @if ($totalPending > 0)
                {{ __('messages.admin_requests.subtitle_pending', ['count' => $totalPending, 'items' => __('messages.admin_requests.' . ($totalPending === 1 ? 'item' : 'items'))]) }}
            @else
                {{ __('messages.admin_requests.subtitle_empty') }}
            @endif
        </p>
    </div>

    @php
        $sections = [
            [
                'key' => 'providers', 'tone' => 'violet',
                'title' => __('messages.admin_requests.provider_approvals'),
                'items' => $pendingProviders, 'empty' => __('messages.admin_requests.empty_providers'),
                'icon' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/>',
            ],
            [
                'key' => 'disputes', 'tone' => 'red',
                'title' => __('messages.admin_requests.open_disputes'),
                'items' => $openDisputes, 'empty' => __('messages.admin_requests.empty_disputes'),
                'icon' => '<path d="M12 9v4M12 16.5v.5" stroke-linecap="round"/><path d="M10.3 3.3 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.3a2 2 0 0 0-3.4 0z" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'contracts', 'tone' => 'amber',
                'title' => __('messages.admin_requests.contracts_awaiting_quote'),
                'items' => $submittedContracts, 'empty' => __('messages.admin_requests.empty_contracts'),
                'icon' => '<path d="M9 4h6l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke-linejoin="round"/><path d="M9 12h6M9 16h6" stroke-linecap="round"/>',
            ],
            [
                'key' => 'applications', 'tone' => 'sky',
                'title' => __('messages.admin_requests.new_applications'),
                'items' => $newApplications, 'empty' => __('messages.admin_requests.empty_applications'),
                'icon' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'key' => 'withdrawals', 'tone' => 'brand',
                'title' => 'Withdrawal requests',
                'items' => $pendingWithdrawals, 'empty' => 'No pending withdrawal requests.',
                'icon' => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h2M3 10h18" stroke-linecap="round"/><path d="M8 16.5l2-2 2 1.5 2-3" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
        ];

        $toneClasses = [
            'violet' => ['bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400', 'bg-violet-500'],
            'red'    => ['bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400', 'bg-red-500'],
            'amber'  => ['bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400', 'bg-amber-500'],
            'sky'    => ['bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400', 'bg-sky-500'],
            'brand'  => ['bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400', 'bg-brand-500'],
        ];

        $pendingSections = collect($sections)->filter(fn ($s) => $s['items']->count() > 0)->values();
    @endphp

    {{-- Overview strip --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($sections as $s)
            <a href="#section-{{ $s['key'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $toneClasses[$s['tone']][0] }}">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7">{!! $s['icon'] !!}</svg>
                </span>
                <p class="mt-3 font-display text-2xl font-extrabold text-slate-900 dark:text-white">{{ $s['items']->count() }}</p>
                <p class="mt-0.5 text-xs font-medium leading-snug text-slate-500 dark:text-slate-400">{{ $s['title'] }}</p>
            </a>
        @endforeach
    </div>

    @if ($pendingSections->isEmpty())
        {{-- All caught up --}}
        <div class="mt-8 flex flex-col items-center rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-16 text-center dark:border-slate-800 dark:bg-slate-900">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">You're all caught up</p>
            <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Nothing needs your attention right now — new approvals, disputes, and requests will land here as they come in.</p>
        </div>
    @else
        <div class="mt-8 space-y-8">
            @foreach ($pendingSections as $s)
                <div id="section-{{ $s['key'] }}" class="scroll-mt-24">
                    <h2 class="flex items-center gap-2.5 font-display text-base font-bold text-slate-900 dark:text-white">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $toneClasses[$s['tone']][0] }}">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">{!! $s['icon'] !!}</svg>
                        </span>
                        {{ $s['title'] }}
                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $s['items']->count() }}</span>
                    </h2>

                    <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        @if ($s['key'] === 'providers')
                            @foreach ($s['items'] as $provider)
                                <a href="{{ route('admin.providers.show', $provider) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                                    <div class="flex items-center gap-3">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $toneClasses[$s['tone']][1] }}"></span>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $provider->business_name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $provider->user->name ?? '—' }} · {{ $provider->city }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $provider->submitted_at?->diffForHumans() ?? $provider->created_at->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        @elseif ($s['key'] === 'disputes')
                            @foreach ($s['items'] as $dispute)
                                <a href="{{ route('admin.disputes.show', $dispute) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                                    <div class="flex items-center gap-3">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $toneClasses[$s['tone']][1] }}"></span>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $dispute->reference }}</p>
                                            <p class="max-w-lg truncate text-xs text-slate-500 dark:text-slate-400">{{ $dispute->reason }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $dispute->created_at->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        @elseif ($s['key'] === 'contracts')
                            @foreach ($s['items'] as $contract)
                                <a href="{{ route('admin.contracts.show', $contract) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                                    <div class="flex items-center gap-3">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $toneClasses[$s['tone']][1] }}"></span>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $contract->title }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $contract->consumer->name ?? '—' }} · {{ $contract->city }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $contract->created_at->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        @elseif ($s['key'] === 'applications')
                            @foreach ($s['items'] as $application)
                                <a href="{{ route('admin.careers.applications.show', ['career' => $application->career_listing_id, 'application' => $application]) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                                    <div class="flex items-center gap-3">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $toneClasses[$s['tone']][1] }}"></span>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $application->jobSeeker->name ?? '—' }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $application->listing->title ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $application->created_at->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        @elseif ($s['key'] === 'withdrawals')
                            @foreach ($s['items'] as $withdrawal)
                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                                    <div class="flex items-center gap-3">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $toneClasses[$s['tone']][1] }}"></span>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $withdrawal->providerProfile->business_name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Rs. {{ number_format($withdrawal->amount, 0) }} · {{ $withdrawal->methodLabel() }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $withdrawal->created_at->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
