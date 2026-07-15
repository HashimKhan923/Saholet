@extends('layouts.app')

@section('title', 'My subscriptions — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My subscriptions</h1>
        </div>
        <a href="{{ route('subscription-plans.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Browse plans</a>
    </div>

    <div class="mt-6 space-y-3">
        @forelse ($subscriptions as $subscription)
            <a href="{{ route('consumer.subscriptions.show', $subscription) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $subscription->plan->name }}</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $subscription->reference }} · Next visit {{ $subscription->next_visit_date->format('d M Y') }}</p>
                    </div>
                    <x-subscription-status :status="$subscription->status" />
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No subscriptions yet</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Browse maintenance plans to set up recurring service — no need to book each visit yourself.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $subscriptions->links() }}</div>
</section>
@endsection
