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
        haystack: @js($categories->flatMap(fn ($c) => $c->services->map(fn ($s) => mb_strtolower($c->name . ' ' . $s->name . ' ' . $s->description)))->values()),
        get hasResults() { return this.q === '' || this.haystack.some(s => s.includes(this.q.toLowerCase())); },
    }">
    @if ($categories->isNotEmpty())
        <div class="relative mb-10 max-w-md">
            <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
            <input type="search" x-model="q" placeholder="Search services or categories…"
                class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>
    @endif

    @forelse ($categories as $category)
        @php
            $categorySearch = mb_strtolower($category->name . ' ' . $category->description);
            $servicesSearch = $category->services->map(fn ($s) => mb_strtolower($s->name . ' ' . $s->description))->all();
        @endphp
        <div class="mb-14 last:mb-0"
            x-show="q === '' || @js($categorySearch).includes(q.toLowerCase()) || @js($servicesSearch).some(s => s.includes(q.toLowerCase()))">
            @if ($category->banner_url)
                <div class="relative overflow-hidden rounded-2xl">
                    <img src="{{ $category->banner_url }}" alt="" class="h-40 w-full object-cover sm:h-48" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 flex items-center gap-3 p-5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white backdrop-blur">
                            <x-service-icon :name="$category->icon" class="h-6 w-6" />
                        </span>
                        <div>
                            <h2 class="font-display text-xl font-extrabold tracking-tight text-white">{{ $category->name }}</h2>
                            @if ($category->description)
                                <p class="text-sm text-white/80">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                        <x-service-icon :name="$category->icon" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $category->name }}</h2>
                        @if ($category->description)
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $category->description }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($category->services as $service)
                    @php $serviceSearch = mb_strtolower($category->name . ' ' . $service->name . ' ' . $service->description); @endphp
                    <a href="{{ route('services.show', $service) }}"
                       x-show="q === '' || @js($serviceSearch).includes(q.toLowerCase())"
                       class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                        @if ($service->thumbnail_url)
                            <img src="{{ $service->thumbnail_url }}" alt="" class="h-32 w-full object-cover" loading="lazy">
                        @endif
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 dark:text-white">{{ $service->name }}</h3>
                            @if ($service->description)
                                <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $service->description }}</p>
                            @endif
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                                <span class="text-sm font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($service->base_price, 0) }}</span>
                                <span class="text-xs text-slate-400">~ {{ $service->duration_minutes }} min</span>
                            </div>
                        </div>
                    </a>
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