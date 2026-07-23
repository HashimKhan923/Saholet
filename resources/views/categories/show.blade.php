@extends('layouts.app')

@section('title', $category->name . ' — ' . config('app.name'))
@section('meta_description', $category->description ?: ('Browse ' . $category->name . ' services on ' . config('app.name')))

@section('content')

<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('services.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-brand-700 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ __('messages.nav.services') }}
        </a>

        <div class="animate-fade-up mt-6">
            <x-category-banner :category="$category" size="lg" />
        </div>

        <div class="animate-fade-up-delayed mt-8">
            <x-service-search :index="$searchIndex" :action="route('categories.show', $category)"
                :placeholder="'Search ' . $category->name . ' services…'" />
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($category->services->isNotEmpty())
        <div class="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($category->services as $service)
                <x-service-card :service="$service" />
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No services yet</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Please check back soon — this category is being set up.</p>
        </div>
    @endif
</section>

@endsection
