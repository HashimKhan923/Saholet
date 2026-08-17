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

    @if ($categories->isNotEmpty())
        @php
            $categoryPalette = [
                'plumbing'   => 'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400',
                'ac'         => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-950/60 dark:text-cyan-400',
                'carpentry'  => 'bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-400',
                'cleaning'   => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400',
                'painting'   => 'bg-violet-50 text-violet-600 dark:bg-violet-950/60 dark:text-violet-400',
                'appliance'  => 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400',
                'pest'       => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                'default'    => 'bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400',
            ];
        @endphp
        <div class="mb-14 grid grid-cols-2 gap-5 lg:grid-cols-4">
            @foreach ($categories as $i => $category)
                @php $iconTone = $categoryPalette[$category->icon] ?? $categoryPalette['default']; @endphp
                <a href="#category-{{ $category->id }}"
                   class="reveal card-lift group relative isolate block aspect-square overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm ring-1 ring-black/5 transition-all duration-300 ease-out hover:-translate-y-1.5 hover:shadow-2xl hover:ring-brand-400/30 dark:border-slate-800 dark:bg-slate-800"
                   style="--reveal-delay: {{ ($i % 4) * 70 }}ms">
                    @if ($category->image_url)
                       <img src="{{ $category->image_url }}" alt="" loading="lazy"
                             class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.08]">
                        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/85 via-20% to-transparent dark:from-slate-950 dark:via-slate-950/85"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-50 to-white dark:from-slate-900 dark:to-slate-800"></div>
                    @endif

                    <span class="absolute right-4 top-4 z-10 flex h-8 w-8 -translate-y-1 items-center justify-center rounded-full bg-white text-brand-600 opacity-0 shadow-sm transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 dark:bg-slate-900 dark:text-brand-400">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:-scale-x-100" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M7 17 17 7M8 7h9v9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>

                    <div class="absolute inset-x-0 bottom-0 z-10 flex flex-col gap-3 p-4 sm:p-5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white shadow-sm transition-transform duration-300 ease-out group-hover:-translate-y-1 group-hover:scale-110 sm:h-11 sm:w-11 dark:border-slate-900 {{ $iconTone }}">
                            <x-service-icon :name="$category->icon" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-display text-base font-bold leading-tight text-slate-900 transition-colors duration-200 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400 sm:text-lg">{{ $category->name }}</p>
                            <p class="mt-1 line-clamp-2 text-xs leading-snug text-slate-500 dark:text-slate-400 sm:text-sm">
                                {{ $category->description ?: trans_choice('messages.landing.services_count', $category->services?->count() ?? 0, ['count' => $category->services?->count() ?? 0]) }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @forelse ($categories as $category)
        @php
            $categorySearch = mb_strtolower($category->name . ' ' . $category->description);
            $servicesSearch = $category->services->map(fn ($s) => mb_strtolower($s->name . ' ' . $s->description))->all();
        @endphp
        <div id="category-{{ $category->id }}" class="mb-14 scroll-mt-44 last:mb-0"
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
