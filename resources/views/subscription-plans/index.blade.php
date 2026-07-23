@extends('layouts.app')

@section('title', 'Maintenance plans — ' . config('app.name'))

@section('content')
<div x-data="{
        q: '',
        haystack: @js($plans->map(fn ($p) => mb_strtolower($p->name . ' ' . ($p->description ?? '') . ' ' . ($p->service->name ?? '') . ' ' . ($p->service->category->name ?? '')))->values()),
        get hasResults() { return this.q === '' || this.haystack.some(s => s.includes(this.q.toLowerCase())); },
    }">
<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>

    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <img src="{{ asset('images/MaintenancePlans.jpeg') }}?v={{ filemtime(public_path('images/MaintenancePlans.jpeg')) }}" alt="Sahoulat maintenance plans" class="animate-fade-up mb-8 h-auto w-full rounded-2xl shadow-sm md:h-100 md:object-cover" loading="eager">
        <span class="animate-fade-up inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-700 shadow-sm dark:bg-slate-900 dark:text-brand-400">Recurring maintenance</span>
        <h1 class="animate-fade-up mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Maintenance plans</h1>
        <p class="animate-fade-up mt-3 max-w-2xl text-slate-600 dark:text-slate-400">Subscribe once and never think about it again — we'll assign a trusted provider and schedule every future visit automatically, right on time.</p>

        @if ($plans->isNotEmpty())
            <div class="animate-fade-up-delayed relative mt-8 max-w-md">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="search" x-model="q" placeholder="Search maintenance plans…"
                    class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        @endif
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($plans->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $i => $plan)
                @php $planSearch = mb_strtolower($plan->name . ' ' . ($plan->description ?? '') . ' ' . ($plan->service->name ?? '') . ' ' . ($plan->service->category->name ?? '')); @endphp
                <div class="reveal card-lift flex flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                     style="--reveal-delay: {{ ($i % 3) * 80 }}ms"
                     x-show="q === '' || @js($planSearch).includes(q.toLowerCase())">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                            <x-service-icon :name="$plan->service->category->icon ?? 'default'" class="h-6 w-6" />
                        </span>
                        <div>
                            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h2>
                            <span class="mt-1 inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $plan->frequencyLabel() }}</span>
                        </div>
                    </div>

                    <p class="mt-4 line-clamp-3 min-h-15 text-sm text-slate-500 dark:text-slate-400">{{ $plan->description ?? $plan->service->name }}</p>
                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                        {{ $plan->service->category->name ?? '' }} · {{ $plan->total_visits ? $plan->total_visits . ' visits' : 'Ongoing, cancel anytime' }}
                    </p>

                    <div class="mt-auto border-t border-slate-100 pt-5 dark:border-slate-800">
                        <p class="font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($plan->price_per_visit, 0) }}</p>
                        <p class="text-xs text-slate-400">per visit</p>
                    </div>

                    <a href="{{ route('consumer.subscriptions.create', $plan) }}" class="btn-shine mt-5 inline-flex items-center justify-center rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Subscribe
                    </a>
                </div>
            @endforeach
        </div>

        <div x-show="q !== '' && !hasResults" x-cloak class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No matches for "<span x-text="q"></span>"</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Try a different search term, or browse all plans above.</p>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No plans available yet</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Please check back soon.</p>
        </div>
    @endif
</section>
</div>
@endsection
