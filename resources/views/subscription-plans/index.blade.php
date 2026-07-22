@extends('layouts.app')

@section('title', 'Maintenance plans — ' . config('app.name'))

@section('content')
<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <img src="{{ asset('images/MaintenancePlans.jpeg') }}?v={{ filemtime(public_path('images/MaintenancePlans.jpeg')) }}" alt="Sahoulat maintenance plans" class="mb-8 h-auto w-full rounded-2xl shadow-sm md:h-100 md:object-cover" loading="eager">
        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-700 shadow-sm dark:bg-slate-900 dark:text-brand-400">Recurring maintenance</span>
        <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Maintenance plans</h1>
        <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">Subscribe once and never think about it again — we'll assign a trusted provider and schedule every future visit automatically, right on time.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @forelse ($plans as $plan)
        <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h2>
                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $plan->frequencyLabel() }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $plan->description ?? $plan->service->name }}</p>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                    {{ $plan->service->category->name ?? '' }} · {{ $plan->total_visits ? $plan->total_visits . ' visits' : 'Ongoing, cancel anytime' }}
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-4">
                <div class="text-right">
                    <p class="font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($plan->price_per_visit, 0) }}</p>
                    <p class="text-xs text-slate-400">per visit</p>
                </div>
                <a href="{{ route('consumer.subscriptions.create', $plan) }}" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Subscribe</a>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No plans available yet</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Please check back soon.</p>
        </div>
    @endforelse
</section>
@endsection
