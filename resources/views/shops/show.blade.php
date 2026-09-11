@extends('layouts.app')

@section('title', $provider->shopName() . ' — ' . config('app.name'))
@section('meta_description', 'Shop ' . $provider->shopName() . ' on Sahoulat.')

@section('content')

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <section class="relative overflow-hidden rounded-3xl border border-brand-100 bg-gradient-to-br from-white via-white to-brand-100 shadow-lg dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-brand-950/40">
        <div class="absolute inset-0 bg-dot-grid opacity-40"></div>

        <div class="relative flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-slate-50 shadow-xl dark:border-slate-800 dark:bg-slate-800">
                    @if ($provider->shopLogoUrl())
                        <img src="{{ $provider->shopLogoUrl() }}" alt="{{ $provider->shopName() }}" class="h-full w-full object-cover">
                    @else
                        <span class="font-display text-2xl font-extrabold text-brand-600 dark:text-brand-400">{{ mb_substr($provider->shopName(), 0, 1) }}</span>
                    @endif
                </div>

                <div class="min-w-0">
                    <h1 class="truncate font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $provider->shopName() }}</h1>
                    @if ($provider->city)
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $provider->city }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Verified
                        </span>
                        @if ((float) $provider->rating_avg > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                                <svg viewBox="0 0 20 20" class="h-3 w-3" fill="currentColor"><path d="M10 1.6l2.5 5.1 5.6.8-4 3.9 1 5.6L10 14.4 5 17l1-5.6-4-3.9 5.6-.8L10 1.6z"/></svg>
                                {{ number_format((float) $provider->rating_avg, 1) }} ({{ $provider->reviews_count }})
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9M3 9h18M9 13a3 3 0 0 0 6 0" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $products->total() }} {{ $products->total() === 1 ? 'product' : 'products' }}
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ route('providers.show', $provider) }}" class="btn-shine inline-flex shrink-0 items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                View more
                <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </section>

    <div class="mt-10">
        <form method="GET" action="{{ route('shops.show', $provider) }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search {{ $provider->shopName() }}'s products…"
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pe-3.5 ps-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            @if ($categories->isNotEmpty())
                <div class="relative">
                    <select name="category" class="appearance-none rounded-xl border border-slate-300 bg-white py-2.5 ps-3.5 pe-9 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <svg viewBox="0 0 24 24" class="pointer-events-none absolute end-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            @endif
            <button type="submit" class="btn-shine rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Search</button>
        </form>

        <div class="mt-8">
            @if ($products->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
                    <svg viewBox="0 0 24 24" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m3 15 4.5-4.5a2 2 0 0 1 2.8 0L15 15M13.5 13.5 15.5 11.5a2 2 0 0 1 2.8 0L21 14.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">No products found.</p>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :wishlisted="($wishlistedIds ?? collect())->contains($product->id)" />
                    @endforeach
                </div>

                <div class="mt-10">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>

@endsection
