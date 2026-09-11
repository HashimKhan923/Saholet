@extends('layouts.app')

@section('title', 'Shops — ' . config('app.name'))
@section('meta_description', 'Browse shops selling parts and products directly on Sahoulat.')

@section('content')

<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="animate-fade-up font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Shops</h1>
        <p class="animate-fade-up mt-3 max-w-2xl text-slate-600 dark:text-slate-400">Browse shops selling parts and products directly on Sahoulat.</p>

        <form method="GET" action="{{ route('shops.index') }}" class="animate-fade-up-delayed mt-8 flex max-w-md gap-3">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search shops…"
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pe-3.5 ps-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <button type="submit" class="btn-shine rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Search</button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($shops->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
            <svg viewBox="0 0 24 24" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9M3 9h18M9 13a3 3 0 0 0 6 0" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">No shops found.</p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($shops as $provider)
                <x-shop-card :provider="$provider" />
            @endforeach
        </div>

        <div class="mt-10">{{ $shops->links() }}</div>
    @endif
</section>

@endsection
