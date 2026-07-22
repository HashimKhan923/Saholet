@extends('layouts.app')

@section('title', 'Careers — ' . config('app.name'))

@section('content')
<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <img src="{{ asset('images/Career.jpeg') }}?v={{ filemtime(public_path('images/Career.jpeg')) }}" alt="Careers at Sahoulat" class="mb-8 h-auto w-full rounded-2xl shadow-sm md:h-100 md:object-cover" loading="eager">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.careers.title') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">{{ __('messages.careers.subtitle') }}</p>

        <form method="GET" action="{{ route('careers.index') }}" class="mt-8 flex flex-wrap gap-3">
            <select name="category" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">{{ __('messages.careers.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="employment_type" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">{{ __('messages.careers.any_type') }}</option>
                @foreach (\App\Models\CareerListing::EMPLOYMENT_TYPES as $type)
                    <option value="{{ $type }}" @selected(($filters['employment_type'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
            <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="{{ __('messages.careers.city_placeholder') }}" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.careers.filter_btn') }}</button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="space-y-3">
        @forelse ($listings as $listing)
            <a href="{{ route('careers.show', $listing) }}"
               class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $listing->category->name }}</span>
                        <h2 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $listing->title }}</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ ucfirst(str_replace('_', ' ', $listing->employment_type)) }}
                            @if ($listing->is_remote) &middot; {{ __('messages.careers.remote_label') }} @endif
                            @if ($listing->city) &middot; {{ $listing->city }} @endif
                        </p>
                    </div>
                    @if ($listing->salary_min || $listing->salary_max)
                        <span class="text-sm font-semibold text-brand-700 dark:text-brand-400">
                            Rs. {{ number_format($listing->salary_min ?? 0, 0) }}@if($listing->salary_max) – {{ number_format($listing->salary_max, 0) }}@endif
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.careers.empty_title') }}</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.careers.empty_desc') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-10">{{ $listings->links() }}</div>
</section>
@endsection
