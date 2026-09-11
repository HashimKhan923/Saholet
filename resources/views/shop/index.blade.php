@extends('layouts.app')

@section('title', ($provider ? $provider->business_name . ' — Products' : 'Products') . ' — ' . config('app.name'))
@section('meta_description', 'Buy parts and products directly from Sahoulat providers.')

@section('content')

<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="animate-fade-up font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
            {{ $provider ? $provider->business_name . "'s products" : 'Products' }}
        </h1>
        <p class="animate-fade-up mt-3 max-w-2xl text-slate-600 dark:text-slate-400">
            {{ $provider ? 'Products sold directly by ' . $provider->business_name . '.' : 'Parts and products sold directly by Sahoulat providers.' }}
        </p>

        <form method="GET" action="{{ route('shop.index') }}" class="animate-fade-up-delayed mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row">
            @if ($provider)
                <input type="hidden" name="provider" value="{{ $provider->id }}">
            @endif
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search products…"
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pe-3.5 ps-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div class="relative">
                <select name="category" class="appearance-none rounded-xl border border-slate-300 bg-white py-2.5 ps-3.5 pe-9 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute end-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <button type="submit" class="btn-shine rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Search</button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
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
</section>

@endsection
