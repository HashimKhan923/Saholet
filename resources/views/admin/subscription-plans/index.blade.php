@extends('layouts.admin')

@section('title', 'Subscription plans — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Subscriptions</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Subscription plans</h1>
        </div>
        <a href="{{ route('admin.subscription-plans.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New plan</a>
    </div>

    <div class="mt-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total plans" :value="$counts['total']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M17 2.1l4 4-4 4M7 21.9l-4-4 4-4" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.5 12a8.5 8.5 0 0 1 14.5-6h-4M20.5 12a8.5 8.5 0 0 1-14.5 6h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Active" :value="$counts['active']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m5 12.5 5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Hidden" :value="$counts['hidden']" tone="amber">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.4 5.5A9.9 9.9 0 0 1 12 5c5 0 9 4 10 7-.4 1.2-1.1 2.5-2.1 3.6M6.6 6.6C4.5 8 3 10 2 12c1 3 5 7 10 7 1.3 0 2.5-.3 3.6-.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </x-stat-card>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Plan</th>
                    <th class="px-5 py-3">Service</th>
                    <th class="px-5 py-3">Frequency</th>
                    <th class="px-5 py-3">Price/visit</th>
                    <th class="px-5 py-3">Subscribers</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($plans as $plan)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $plan->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $plan->service->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $plan->frequencyLabel() }}{{ $plan->total_visits ? " · {$plan->total_visits} visits" : ' · ongoing' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format($plan->price_per_visit, 0) }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $plan->subscriptions_count }}</td>
                        <td class="px-5 py-3">
                            @if ($plan->is_active)
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Hidden</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Edit</a>
                                <x-confirm-form :action="route('admin.subscription-plans.destroy', $plan)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this plan?" confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No plans yet. Create your first one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
