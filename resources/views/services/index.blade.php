@extends('layouts.app')

@section('title', 'Services — ' . config('app.name'))

@section('content')
<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <img src="{{ asset('images/Services.jpeg') }}?v={{ filemtime(public_path('images/Services.jpeg')) }}" alt="Sahoulat services" class="mb-8 h-auto w-full rounded-2xl shadow-sm md:h-100 md:object-cover" loading="eager">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Browse services</h1>
        <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">Verified professionals across Pakistan. Explore categories below and view details for each service.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"
    x-data="{
        q: '',
        open: false,
        items: @js($searchIndex),
        haystack: @js($categories->flatMap(fn ($c) => $c->services->map(fn ($s) => mb_strtolower($c->name . ' ' . $s->name . ' ' . $s->description)))->values()),
        get matches() {
            const term = this.q.trim().toLowerCase();
            if (term.length < 2) return [];
            return this.items.filter(i => i.haystack.includes(term)).slice(0, 8);
        },
        get hasResults() { return this.q === '' || this.haystack.some(s => s.includes(this.q.toLowerCase())); },
    }">
    @if ($categories->isNotEmpty())
        <div class="relative mb-10 max-w-lg" @click.outside="open = false">
            <form action="{{ route('services.index') }}" method="GET">
                <div class="flex items-center overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm transition-colors focus-within:border-brand-600 dark:border-slate-700 dark:bg-slate-900">
                    <svg viewBox="0 0 24 24" class="ms-4 h-4 w-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                    <input type="search" name="q" x-model="q" @focus="open = true" autocomplete="off" placeholder="Search services or categories…"
                        class="min-w-0 flex-1 bg-transparent px-3 py-3.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-white">
                    <button type="submit" class="flex-shrink-0 bg-brand-600 px-5 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700">
                        {{ __('messages.landing.search_btn') }}
                    </button>
                </div>
            </form>

            {{-- Live suggestions --}}
            <div x-show="open && matches.length" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-900">
                <template x-for="item in matches" :key="item.url">
                    <a :href="item.url" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-brand-50 dark:text-slate-200 dark:hover:bg-brand-950/40">
                        <span x-text="item.name"></span>
                        <span class="shrink-0 text-xs text-slate-400" x-text="item.category"></span>
                    </a>
                </template>
            </div>
        </div>
    @endif

    @forelse ($categories as $category)
        @php
            $categorySearch = mb_strtolower($category->name . ' ' . $category->description);
            $servicesSearch = $category->services->map(fn ($s) => mb_strtolower($s->name . ' ' . $s->description))->all();
        @endphp
        <div class="mb-14 last:mb-0"
            x-show="q === '' || @js($categorySearch).includes(q.toLowerCase()) || @js($servicesSearch).some(s => s.includes(q.toLowerCase()))">

            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex-1">
                    <x-category-banner :category="$category" size="sm" />
                </div>
                <a href="{{ route('categories.show', $category) }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                    View category
                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($category->services as $service)
                    @php $serviceSearch = mb_strtolower($category->name . ' ' . $service->name . ' ' . $service->description); @endphp
                    <div x-show="q === '' || @js($serviceSearch).includes(q.toLowerCase())">
                        <x-service-card :service="$service" />
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No services yet</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Please check back soon — our catalog is being set up.</p>
        </div>
    @endforelse

    <div x-show="q !== '' && !hasResults" x-cloak class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
        <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No matches for "<span x-text="q"></span>"</p>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Try a different search term, or browse all categories above.</p>
    </div>
</section>
@endsection
