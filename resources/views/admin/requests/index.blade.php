@extends('layouts.admin')

@section('title', 'Requests inbox — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
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

    <div class="mt-8 space-y-8">
        {{-- Provider approvals --}}
        <div>
            <h2 class="flex items-center gap-2 font-display text-base font-bold text-slate-900 dark:text-white">
                {{ __('messages.admin_requests.provider_approvals') }}
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $pendingProviders->count() }}</span>
            </h2>
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @forelse ($pendingProviders as $provider)
                    <a href="{{ route('admin.providers.show', $provider) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $provider->business_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $provider->user->name ?? '—' }} · {{ $provider->city }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $provider->submitted_at?->diffForHumans() ?? $provider->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">{{ __('messages.admin_requests.empty_providers') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Open disputes --}}
        <div>
            <h2 class="flex items-center gap-2 font-display text-base font-bold text-slate-900 dark:text-white">
                {{ __('messages.admin_requests.open_disputes') }}
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $openDisputes->count() }}</span>
            </h2>
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @forelse ($openDisputes as $dispute)
                    <a href="{{ route('admin.disputes.show', $dispute) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $dispute->reference }}</p>
                            <p class="max-w-lg truncate text-xs text-slate-500 dark:text-slate-400">{{ $dispute->reason }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $dispute->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">{{ __('messages.admin_requests.empty_disputes') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Contracts awaiting a quote --}}
        <div>
            <h2 class="flex items-center gap-2 font-display text-base font-bold text-slate-900 dark:text-white">
                {{ __('messages.admin_requests.contracts_awaiting_quote') }}
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $submittedContracts->count() }}</span>
            </h2>
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @forelse ($submittedContracts as $contract)
                    <a href="{{ route('admin.contracts.show', $contract) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $contract->title }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $contract->consumer->name ?? '—' }} · {{ $contract->city }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $contract->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">{{ __('messages.admin_requests.empty_contracts') }}</p>
                @endforelse
            </div>
        </div>

        {{-- New career applications --}}
        <div>
            <h2 class="flex items-center gap-2 font-display text-base font-bold text-slate-900 dark:text-white">
                {{ __('messages.admin_requests.new_applications') }}
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $newApplications->count() }}</span>
            </h2>
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @forelse ($newApplications as $application)
                    <a href="{{ route('admin.careers.applications.show', ['career' => $application->career_listing_id, 'application' => $application]) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $application->jobSeeker->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $application->listing->title ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $application->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">{{ __('messages.admin_requests.empty_applications') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Withdrawal requests --}}
        <div>
            <h2 class="flex items-center gap-2 font-display text-base font-bold text-slate-900 dark:text-white">
                Withdrawal requests
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $pendingWithdrawals->count() }}</span>
            </h2>
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @forelse ($pendingWithdrawals as $withdrawal)
                    <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 text-sm transition last:border-0 hover:bg-slate-50/60 dark:border-slate-800 dark:hover:bg-slate-800/60">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $withdrawal->providerProfile->business_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Rs. {{ number_format($withdrawal->amount, 0) }} · {{ $withdrawal->methodLabel() }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $withdrawal->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">No pending withdrawal requests.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
