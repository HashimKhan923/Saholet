@extends('layouts.app')

@section('title', 'Careers — ' . config('app.name'))

@section('content')
<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <img src="{{ asset('images/Career.jpeg') }}?v={{ filemtime(public_path('images/Career.jpeg')) }}" alt="Careers at Sahoulat" class="mb-8 h-auto w-full rounded-2xl shadow-sm md:h-100 md:object-cover" loading="eager">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.careers.title') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">{{ __('messages.careers.subtitle') }}</p>

        <form method="GET" action="{{ route('careers.index') }}" class="mt-8 flex flex-wrap gap-3">
            <div class="relative">
                <select name="category" class="appearance-none rounded-lg border border-slate-300 bg-white py-2.5 ps-3.5 pe-9 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">{{ __('messages.careers.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute end-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="relative">
                <select name="employment_type" class="appearance-none rounded-lg border border-slate-300 bg-white py-2.5 ps-3.5 pe-9 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">{{ __('messages.careers.any_type') }}</option>
                    @foreach (\App\Models\CareerListing::EMPLOYMENT_TYPES as $type)
                        <option value="{{ $type }}" @selected(($filters['employment_type'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute end-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="{{ __('messages.careers.city_placeholder') }}" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.careers.filter_btn') }}</button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($listings->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.careers.empty_title') }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.careers.empty_desc') }}</p>
        </div>
    @else
        <div x-data="{ activeId: {{ old('_listing_id') ?: $listings->first()->id }} }" class="lg:grid lg:grid-cols-5 lg:items-start lg:gap-8">

            {{-- LEFT: scrollable job list --}}
            <div class="space-y-3 lg:col-span-2 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:pe-2">
                @foreach ($listings as $listing)
                    <a href="{{ route('careers.show', $listing) }}"
                       @click="if (window.innerWidth >= 1024) { $event.preventDefault(); activeId = {{ $listing->id }} }"
                       class="block rounded-xl border p-5 shadow-sm transition hover:shadow-md dark:hover:border-brand-800"
                       :class="activeId === {{ $listing->id }}
                            ? 'border-brand-400 ring-2 ring-brand-100 bg-brand-50/40 dark:border-brand-700 dark:ring-brand-900/40 dark:bg-brand-950/20'
                            : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900'">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $listing->category->name }}</span>
                                <h2 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $listing->title }}</h2>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ ucfirst(str_replace('_', ' ', $listing->employment_type)) }}
                                    @if ($listing->is_remote) &middot; {{ __('messages.careers.remote_label') }} @endif
                                    @if ($listing->city) &middot; {{ $listing->city }} @endif
                                </p>
                                @if (in_array($listing->id, $appliedIds))
                                    <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 dark:text-brand-400">
                                        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        {{ __('messages.careers.already_applied_short') }}
                                    </span>
                                @endif
                            </div>
                            @if ($listing->salary_min || $listing->salary_max)
                                <span class="shrink-0 text-sm font-semibold text-brand-700 dark:text-brand-400">
                                    Rs. {{ number_format($listing->salary_min ?? 0, 0) }}@if($listing->salary_max) – {{ number_format($listing->salary_max, 0) }}@endif
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- RIGHT: detail + apply panel (desktop only — mobile navigates to careers.show instead) --}}
            <div class="mt-8 hidden lg:col-span-3 lg:sticky lg:top-24 lg:mt-0 lg:block">
                @foreach ($listings as $listing)
                    <div x-show="activeId === {{ $listing->id }}" x-cloak>
                        @include('careers._job-detail', ['listing' => $listing, 'headingTag' => 'h2'])
                        @include('careers._apply-panel', ['listing' => $listing, 'hasApplied' => in_array($listing->id, $appliedIds)])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
