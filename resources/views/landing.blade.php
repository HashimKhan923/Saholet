@extends('layouts.app')

@section('title', config('app.name') . ' — On-demand home services across Pakistan')

@push('jsonld')
@php
    $orgSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => config('app.name'),
        'url' => url('/'),
        'image' => asset('images/Logo.png'),
        'description' => 'On-demand home services across Pakistan — AC repair, plumbing, electrical, cleaning and more. Verified professionals, instant booking, secure payments.',
        'areaServed' => ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 'Hyderabad', 'Peshawar', 'Quetta', 'Sialkot'],
    ];
    if ($stats['rating'] > 0) {
        $orgSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $stats['rating'],
            'reviewCount' => (string) max($stats['bookings'], 1),
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

{{-- ============================================================= Hero --}}
@php
    $isUrdu = app()->getLocale() === 'ur';
@endphp
<section class="relative overflow-hidden bg-dot-grid">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 py-14 lg:grid-cols-3 lg:gap-8 lg:py-20">

            {{-- ── LEFT ── --}}
            <div class="animate-fade-up {{ $isUrdu ? 'text-right' : '' }}">
                <div class="mb-5 inline-flex items-center gap-2 {{ $isUrdu ? 'flex-row-reverse' : '' }}">
                    <span class="h-0.5 w-6 rounded bg-accent-600"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-accent-600 {{ $isUrdu ? 'font-urdu text-sm not-italic tracking-normal' : '' }}">
                        {{ __('messages.hero.eyebrow') }}
                    </span>
                </div>

                <div class="mb-3 font-display text-4xl font-bold leading-tight text-slate-900 dark:text-white sm:text-4xl">
                    {{ __('messages.hero.line1') }}
                    <br>
                    <span class="text-brand-600">{{ __('messages.hero.line2') }}</span>
                    <br>
                    <span class=" text-accent-600">{{ __('messages.hero.line3') }}</span>
                </div>

               
                <p class="mb-7 max-w-lg text-base leading-relaxed text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-lg leading-loose' : '' }}">
                    {{ __('messages.hero.subtitle') }}
                </p>

                {{-- Search bar --}}
                <form action="{{ route('services.index') }}" method="GET" class="mb-4 max-w-lg">
                    <div class="flex items-center overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm transition-colors focus-within:border-brand-600 dark:border-slate-700 dark:bg-slate-900">
                        <svg viewBox="0 0 24 24" class="ms-4 h-4 w-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                        <input type="search" name="q" placeholder="{{ __('messages.landing.search_placeholder') }}"
                            class="min-w-0 flex-1 bg-transparent px-3 py-3.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-white {{ $isUrdu ? 'font-urdu text-right text-base' : '' }}">
                        <button type="submit" class="flex-shrink-0 bg-brand-600 px-5 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 {{ $isUrdu ? 'font-urdu text-base' : '' }}">
                            {{ __('messages.landing.search_btn') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── RIGHT: image + overlays ── --}}
            <div class="relative col-span-1 lg:col-span-2">
                <div class="relative overflow-hidden rounded-3xl dark:border-slate-800">
                    <img src="/images/Hero.png" alt="Sahoulat Hero" class="w-full object-cover" loading="eager">
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-800/60 via-transparent to-transparent"></div>

                    
                </div>

                {{-- Floating card 1: verified pro --}}
                <div class="absolute -top-4 left-3 flex max-w-[180px] items-center gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-xl dark:border-slate-700 dark:bg-slate-900 md:-left-4">
                    <span class="grid h-9 w-9 flex-shrink-0 place-items-center rounded-xl bg-brand-100 dark:bg-brand-900/40">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.5 11 14.5 15.5 10" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
                    </span>
                    <div>
                        <p class="mb-0.5 text-xs font-bold leading-none text-slate-900 dark:text-white">Verified Pro</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Background checked</p>
                    </div>
                </div>

                {{-- Floating card 2: booking --}}
                <div class="absolute -bottom-4 right-2 flex max-w-[180px] items-center gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-xl dark:border-slate-700 dark:bg-slate-900 md:-right-4">
                    <span class="grid h-9 w-9 flex-shrink-0 place-items-center rounded-xl bg-accent-50 dark:bg-accent-900/30">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-accent-600" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 11h16M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div>
                        <p class="mb-0.5 text-xs font-bold leading-none text-slate-900 dark:text-white">Book in 2 min</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Same-day available</p>
                    </div>
                </div>

                {{-- Rating card --}}
                <div class="absolute right-3 top-1/2 -translate-y-1/2 rounded-2xl border border-slate-100 bg-white p-3 shadow-xl dark:border-slate-700 dark:bg-slate-900 md:-right-5">
                    <div class="mb-0.5 flex gap-0.5 text-sm text-amber-400" aria-hidden="true">★★★★★</div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $stats['rating'] > 0 ? number_format($stats['rating'], 1) : '4.9' }} / 5.0</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ max($stats['bookings'], 500) }}+ reviews</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================== Cities marquee --}}
<section class="border-y border-slate-100 bg-white py-6 dark:border-slate-800 dark:bg-slate-950" aria-label="{{ __('messages.landing.cities_label') }}">
    <p class="mb-4 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('messages.landing.cities_label') }}</p>
    <div class="marquee-mask overflow-hidden" dir="ltr">
        <div class="animate-marquee flex w-max items-center gap-10">
            @foreach ([1, 2] as $pass)
@foreach ([
    'DHA',
    'DHA City',
    'Bahria Town',
    'Scheme 33',
    'Gulistan-e-Jauhar',
    'Gulshan-e-Iqbal',
    'Bahadurabad',
    'PECHS',
    'North Nazimabad',
    'Nazimabad',
    'Clifton',   
] as $city)                    <span class="inline-flex items-center gap-2 whitespace-nowrap font-display text-lg font-bold text-slate-400 dark:text-slate-700" @if($pass === 2) aria-hidden="true" @endif>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-brand-300 dark:text-brand-800" fill="currentColor"><circle cx="12" cy="9" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" opacity=".35"/></svg>
                        {{ $city }}
                    </span>
                @endforeach
            @endforeach
        </div>
    </div>
</section>

{{-- ======================================================= Stats band --}}
<section class="bg-gradient-to-r from-brand-700 via-brand-600 to-brand-700 dark:from-brand-900 dark:via-brand-800 dark:to-brand-900">
    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-4 py-12 sm:px-6 md:grid-cols-4 lg:px-8">
        @php
            $statItems = [
                ['value' => max($stats['pros'], 250), 'suffix' => '+', 'decimals' => 0, 'label' => __('messages.landing.stats_pros')],
                ['value' => max($stats['bookings'], 1200), 'suffix' => '+', 'decimals' => 0, 'label' => __('messages.landing.stats_bookings')],
                ['value' => max($stats['cities'], 10), 'suffix' => '+', 'decimals' => 0, 'label' => __('messages.landing.stats_cities')],
                ['value' => $stats['rating'], 'suffix' => ' ★', 'decimals' => 1, 'label' => __('messages.landing.stats_rating')],
            ];
        @endphp
        @foreach ($statItems as $i => $stat)
            <div class="reveal text-center" style="--reveal-delay: {{ $i * 90 }}ms">
                <p class="font-display text-3xl font-extrabold text-white sm:text-4xl"
                   data-counter="{{ $stat['value'] }}" data-counter-suffix="{{ $stat['suffix'] }}" data-counter-decimals="{{ $stat['decimals'] }}">0</p>
                <p class="mt-1.5 text-sm font-medium text-brand-100">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ======================================================== Services --}}
<section id="services" class="py-16 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-end">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600">{{ __('messages.nav.services') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.services_title') }}</h2>
                <p class="mt-3 text-slate-600 dark:text-slate-400">{{ __('messages.landing.services_sub') }}</p>
            </div>
            <a href="{{ route('services.index') }}" class="btn-shine group inline-flex shrink-0 items-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                {{ __('messages.landing.browse_all') }}
                <svg viewBox="0 0 24 24" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        @php
            $categoryPalette = [
                'electrical' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400',
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
        <div class="mt-10 grid grid-cols-2 gap-5 lg:grid-cols-4">
            @forelse ($categories as $i => $category)
                @php $iconTone = $categoryPalette[$category->icon] ?? $categoryPalette['default']; @endphp
                <a href="{{ route('services.index') }}"
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
            @empty
                <p class="col-span-full text-sm text-slate-500 dark:text-slate-400">{{ __('messages.landing.services_sub') }}</p>
            @endforelse
        </div>

        <div class="reveal mt-8">
            <a href="{{ route('services.index') }}" class="btn-shine group inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                {{ __('messages.landing.browse_all') }}
                <svg viewBox="0 0 24 24" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </div>
</section>


{{-- ================================================= Three flows band --}}
<section class="border-t border-slate-100 bg-white py-16 dark:border-slate-800 dark:bg-slate-950 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.flows_title') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">{{ __('messages.landing.flows_sub') }}</p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Flow A — direct booking --}}
            <div class="reveal card-lift rounded-3xl border border-slate-200 bg-gradient-to-b from-brand-50/70 to-white p-7 dark:border-slate-800 dark:from-brand-950/40 dark:to-slate-900">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-md shadow-brand-600/30">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 11h16" stroke-linecap="round"/></svg>
                </span>
                <h3 class="mt-5 font-display text-xl font-bold text-slate-900 dark:text-white">{{ __('messages.landing.flow_direct_title') }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.landing.flow_direct_desc') }}</p>
                <a href="{{ route('services.index') }}" class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                    {{ __('messages.landing.flow_direct_cta') }} <span aria-hidden="true" class="rtl:hidden">→</span><span aria-hidden="true" class="hidden rtl:inline">←</span>
                </a>
            </div>

            {{-- Flow B — post & bid --}}
            <div class="reveal card-lift rounded-3xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-7 dark:border-slate-800 dark:from-slate-900/70 dark:to-slate-900" style="--reveal-delay: 90ms">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-white shadow-md shadow-slate-800/30 dark:bg-slate-700">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h3 class="mt-5 font-display text-xl font-bold text-slate-900 dark:text-white">{{ __('messages.landing.flow_bid_title') }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.landing.flow_bid_desc') }}</p>
                <a href="{{ auth()->check() && auth()->user()->isConsumer() ? route('consumer.jobs.create') : route('register') }}" class="mt-5 inline-flex items-center gap-1.5 rounded-lg border-2 border-slate-800 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-800 hover:text-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    {{ __('messages.landing.flow_bid_cta') }} <span aria-hidden="true" class="rtl:hidden">→</span><span aria-hidden="true" class="hidden rtl:inline">←</span>
                </a>
            </div>

            {{-- Flow D — contracts (multi-service projects) --}}
            <div class="reveal card-lift rounded-3xl border border-slate-200 bg-gradient-to-b from-sky-50/70 to-white p-7 dark:border-slate-800 dark:from-sky-950/30 dark:to-slate-900" style="--reveal-delay: 140ms">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-600 text-white shadow-md shadow-sky-600/30">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 4h6l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke-linejoin="round"/><path d="M9 12h6M9 16h6" stroke-linecap="round"/></svg>
                </span>
                <h3 class="mt-5 font-display text-xl font-bold text-slate-900 dark:text-white">{{ __('messages.landing.flow_contract_title') }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.landing.flow_contract_desc') }}</p>
                <a href="{{ auth()->check() && auth()->user()->isConsumer() ? route('consumer.contracts.create') : route('register') }}" class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    {{ __('messages.landing.flow_contract_cta') }} <span aria-hidden="true" class="rtl:hidden">→</span><span aria-hidden="true" class="hidden rtl:inline">←</span>
                </a>
            </div>

            {{-- Flow C — emergency: the one place red owns the card --}}
            <div class="reveal card-lift rounded-3xl border border-accent-200 bg-gradient-to-b from-accent-50/80 to-white p-7 dark:border-accent-900/60 dark:from-accent-950/40 dark:to-slate-900" style="--reveal-delay: 180ms">
                <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-accent-600 text-white shadow-md shadow-accent-600/30">
                    <span class="animate-ping-ring absolute inset-0 rounded-2xl bg-accent-500/60"></span>
                    <svg viewBox="0 0 24 24" class="relative h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z" stroke-linejoin="round"/></svg>
                </span>
                <h3 class="mt-5 font-display text-xl font-bold text-slate-900 dark:text-white">{{ __('messages.landing.flow_sos_title') }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.landing.flow_sos_desc') }}</p>
                <a href="{{ auth()->check() && auth()->user()->isConsumer() ? route('consumer.emergencies.create') : route('register') }}" class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-accent-700">
                    {{ __('messages.landing.flow_sos_cta') }} <span aria-hidden="true" class="rtl:hidden">→</span><span aria-hidden="true" class="hidden rtl:inline">←</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ==================================================== How it works --}}
<section id="how" class="bg-brand-50/60 py-16 dark:bg-slate-900/40 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mb-12 max-w-2xl {{ $isUrdu ? 'text-right' : '' }}">
            <div class="mb-3 inline-flex items-center gap-2 {{ $isUrdu ? 'flex-row-reverse' : '' }}">
                <span class="h-0.5 w-5 rounded bg-accent-600"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-accent-600 {{ $isUrdu ? 'font-urdu text-sm not-italic tracking-normal' : '' }}">{{ __('messages.landing.how_eyebrow') }}</span>
            </div>
            <h2 class="mb-3 font-display text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.how_title') }}</h2>
            <p class="max-w-xl text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu ml-auto text-lg leading-loose' : '' }}">{{ __('messages.landing.how_sub') }}</p>
        </div>

        @php
            $steps = [
                ['icon' => 'search', 'tone' => 'green', 'title' => __('messages.landing.step1_title'), 'desc' => __('messages.landing.step1_desc')],
                ['icon' => 'list',   'tone' => 'red',   'title' => __('messages.landing.step2_title'), 'desc' => __('messages.landing.step2_desc')],
                ['icon' => 'check',  'tone' => 'green', 'title' => __('messages.landing.step3_title'), 'desc' => __('messages.landing.step3_desc')],
                ['icon' => 'star',   'tone' => 'red',   'title' => __('messages.landing.step4_title'), 'desc' => __('messages.landing.step4_desc')],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $i => $step)
                <div class="reveal card-lift relative rounded-2xl border border-slate-200 bg-white p-6 transition-all hover:border-brand-400 hover:shadow-md dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: {{ $i * 90 }}ms">
                    <span class="absolute right-5 top-4 select-none font-display text-5xl font-bold leading-none text-slate-200 dark:text-slate-800">{{ sprintf('%02d', $i + 1) }}</span>

                    <span class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl {{ $step['tone'] === 'green' ? 'bg-brand-50 dark:bg-brand-950/50' : 'bg-accent-50 dark:bg-accent-950/40' }}">
                        @switch($step['icon'])
                            @case('search')
                                <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $step['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                                @break
                            @case('list')
                                <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $step['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @break
                            @case('check')
                                <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $step['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @break
                            @case('star')
                                <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $step['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                                @break
                        @endswitch
                    </span>

                    <h3 class="mb-2 text-sm font-semibold text-slate-900 dark:text-white {{ $isUrdu ? 'font-urdu text-right text-base' : '' }}">{{ $step['title'] }}</h3>
                    <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-right text-sm leading-loose' : '' }}">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <img src="/images/HeroBanner.jpeg" alt="Sahoulat professionals at work" class="mt-8 w-full rounded-2xl object-cover shadow-sm" loading="lazy">
    </div>
</section>

{{-- ======================================================== Why Sahoulat --}}
<section id="why-us" class="bg-white py-16 dark:bg-slate-950 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-14 lg:grid-cols-2">
            {{-- Left --}}
            <div class="reveal {{ $isUrdu ? 'text-right' : '' }}">
                <div class="mb-3 inline-flex items-center gap-2 {{ $isUrdu ? 'flex-row-reverse' : '' }}">
                    <span class="h-0.5 w-5 rounded bg-accent-600"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-accent-600 {{ $isUrdu ? 'font-urdu text-sm not-italic tracking-normal' : '' }}">{{ __('messages.landing.why_eyebrow') }}</span>
                </div>
                <h2 class="mb-3 font-display text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.why_title') }}</h2>
                <p class="mb-8 text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-lg leading-loose' : '' }}">{{ __('messages.landing.why_sub') }}</p>

                @php
                    $reasons = [
                        ['icon' => 'user', 'tone' => 'green', 'title' => __('messages.landing.why_r1_t'), 'desc' => __('messages.landing.why_r1_d')],
                        ['icon' => 'dollar', 'tone' => 'red', 'title' => __('messages.landing.why_r2_t'), 'desc' => __('messages.landing.why_r2_d')],
                        ['icon' => 'clock', 'tone' => 'green', 'title' => __('messages.landing.why_r3_t'), 'desc' => __('messages.landing.why_r3_d')],
                        ['icon' => 'shield', 'tone' => 'red', 'title' => __('messages.landing.why_r4_t'), 'desc' => __('messages.landing.why_r4_d')],
                    ];
                @endphp

                <div class="flex flex-col gap-4">
                    @foreach ($reasons as $r)
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 p-4 transition-colors hover:border-brand-400 dark:border-slate-800 {{ $isUrdu ? 'flex-row-reverse' : '' }}">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $r['tone'] === 'green' ? 'bg-brand-50 dark:bg-brand-950/50' : 'bg-accent-50 dark:bg-accent-950/40' }}">
                                @switch($r['icon'])
                                    @case('user')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $r['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0" stroke-linecap="round"/></svg>
                                        @break
                                    @case('dollar')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $r['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('clock')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $r['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('shield')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5 {{ $r['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3z" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <div class="{{ $isUrdu ? 'flex-1 text-right' : '' }}">
                                <p class="mb-1 text-sm font-semibold text-slate-900 dark:text-white {{ $isUrdu ? 'font-urdu text-base' : '' }}">{{ $r['title'] }}</p>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-sm leading-loose' : '' }}">{{ $r['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: banner image --}}
            <div class="reveal self-center overflow-hidden rounded-3xl ">
                <img src="/images/why.jpeg" alt="Sahoulat professional team" class="h-full w-full object-cover">
            </div>
        </div>
    </div>
</section>

{{-- ============================================== Subscription plans band --}}
<section class="border-t border-slate-100 py-16 dark:border-slate-800 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
        <div class="reveal  overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-800 p-10 shadow-2xl shadow-brand-900/30 ring-1 ring-white/10 sm:p-14">

            

            <div class="relative flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-white">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 2.1l4 4-4 4M7 21.9l-4-4 4-4" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.5 12a8.5 8.5 0 0 1 14.5-6h-4M20.5 12a8.5 8.5 0 0 1-14.5 6h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('messages.nav.plans') }}
                    </span>
                    <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Set it and forget it</h2>
                    <p class="mt-3 text-brand-50">Subscribe to a recurring maintenance plan — AC servicing, generator upkeep, and more — and we'll schedule and assign a trusted provider automatically, every time.</p>
                </div>
                <a href="{{ route('subscription-plans.index') }}"
                   class="btn-shine group relative inline-flex shrink-0 items-center gap-2 rounded-xl bg-white px-7 py-4 text-base font-bold text-brand-800 shadow-xl shadow-black/20 transition hover:scale-105 hover:bg-brand-50">
                    Browse plans
                    <svg viewBox="0 0 24 24" class="h-4 w-4 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ==================================================== Join as Pro --}}
<section id="join" class="bg-brand-50/60 py-16 dark:bg-slate-900/40 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
            {{-- Left: image --}}
            <div class="relative order-2 lg:order-1">
                <div class="reveal overflow-hidden rounded-3xl shadow-xl">
                    <img src="{{ asset('images/JoinBanner.jpeg') }}" alt="Sahoulat service professional at work" class="w-full object-cover">
                </div>
            </div>

            {{-- Right: content --}}
            <div class="reveal order-1 lg:order-2 {{ $isUrdu ? 'text-right' : '' }}">
                <div class="mb-3 inline-flex items-center gap-2 {{ $isUrdu ? 'flex-row-reverse' : '' }}">
                    <span class="h-0.5 w-5 rounded bg-brand-600"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-600 {{ $isUrdu ? 'font-urdu text-sm not-italic tracking-normal' : '' }}">{{ __('messages.landing.join_eyebrow') }}</span>
                </div>
                <h2 class="mb-4 font-display text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.join_title') }}</h2>
                <p class="mb-8 leading-relaxed text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-lg leading-loose' : '' }}">{{ __('messages.landing.join_sub') }}</p>

                @php
                    $joinBenefits = [
                        ['icon' => 'check', 'tone' => 'green', 'title' => __('messages.landing.join_b1_t'), 'desc' => __('messages.landing.join_b1_d')],
                        ['icon' => 'dollar', 'tone' => 'red', 'title' => __('messages.landing.join_b2_t'), 'desc' => __('messages.landing.join_b2_d')],
                        ['icon' => 'zap', 'tone' => 'green', 'title' => __('messages.landing.join_b3_t'), 'desc' => __('messages.landing.join_b3_d')],
                        ['icon' => 'star', 'tone' => 'red', 'title' => __('messages.landing.join_b4_t'), 'desc' => __('messages.landing.join_b4_d')],
                    ];
                @endphp

                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($joinBenefits as $b)
                        <div class="flex items-start gap-3 rounded-xl border border-slate-100 bg-white p-4 transition-colors hover:border-brand-400 dark:border-slate-800 dark:bg-slate-900 {{ $isUrdu ? 'flex-row-reverse' : '' }}">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $b['tone'] === 'green' ? 'bg-brand-50 dark:bg-brand-950/50' : 'bg-accent-50 dark:bg-accent-950/40' }}">
                                @switch($b['icon'])
                                    @case('check')
                                        <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $b['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('dollar')
                                        <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $b['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('zap')
                                        <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $b['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="currentColor"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>
                                        @break
                                    @case('star')
                                        <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $b['tone'] === 'green' ? 'text-brand-600' : 'text-accent-600' }}" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <div class="{{ $isUrdu ? 'text-right' : '' }}">
                                <p class="mb-0.5 text-xs font-semibold text-slate-900 dark:text-white {{ $isUrdu ? 'font-urdu text-sm' : '' }}">{{ $b['title'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu leading-loose' : '' }}">{{ $b['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center {{ $isUrdu ? 'sm:flex-row-reverse' : '' }}">
                    <a href="{{ route('register') }}" class="btn-shine inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700 {{ $isUrdu ? 'font-urdu flex-row-reverse text-base' : '' }}">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0" stroke-linecap="round"/><path d="M19 8v6M16 11h6" stroke-linecap="round"/></svg>
                        {{ __('messages.landing.join_cta') }}
                    </a>
                    <span class="text-xs text-slate-500 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-sm' : '' }}">{{ __('messages.landing.join_cta_sub') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ==================================================== Testimonials --}}
<section class="border-t border-slate-100 bg-white py-16 dark:border-slate-800 dark:bg-slate-950 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.testimonials_title') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">{{ __('messages.landing.testimonials_sub') }}</p>
        </div>

        @php
            $fallbackTestimonials = collect([
                ['name' => 'Ayesha K.', 'service' => 'AC Repair — Karachi', 'comment' => 'Technician arrived on time, price matched the quote exactly, and I could watch him approach on the live map. Booked again the next week.'],
                ['name' => 'Muhammad B.', 'service' => 'Plumbing — Lahore', 'comment' => 'Posted my job at 9am, had four bids by lunch, and the leak was fixed before dinner. The chat inside the booking made everything easy.'],
                ['name' => 'Sana R.', 'service' => 'Deep Cleaning — Islamabad', 'comment' => 'Finally a service where the pros are actually verified. Professional team, upfront pricing, and paying by JazzCash took seconds.'],
            ]);
            $cards = $testimonials->count() >= 3
                ? $testimonials->map(fn ($r) => ['name' => $r->consumer?->name ?? 'Verified customer', 'service' => $r->service?->name ?? '', 'comment' => $r->comment])
                : $fallbackTestimonials;
            $avatarColors = ['#1A7A35', '#C0272D', '#4F46E5'];
        @endphp

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($cards as $i => $card)
                <figure class="reveal card-lift flex flex-col rounded-2xl border border-slate-200 bg-white p-6 transition-all hover:border-brand-400 hover:shadow-md dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: {{ $i * 100 }}ms">
                    <div class="mb-4 flex gap-0.5 text-amber-400" aria-label="5 out of 5 stars">
                        @for ($s = 0; $s < 5; $s++)
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                        @endfor
                    </div>
                    <blockquote class="flex-1 text-sm italic leading-relaxed text-slate-500 dark:text-slate-400">“{{ $card['comment'] }}”</blockquote>
                    <figcaption class="mt-6 flex items-center gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-bold text-white" style="background: {{ $avatarColors[$i % 3] }}">{{ mb_substr($card['name'], 0, 1) }}</span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $card['name'] }}</p>
                            @if ($card['service'])
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $card['service'] }}</p>
                            @endif
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ======================================================= Careers band --}}
<section class="border-t border-slate-100 py-16 dark:border-slate-800 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal relative overflow-hidden rounded-3xl border-2 border-brand-500/40 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-10 shadow-2xl shadow-brand-900/20 sm:p-14 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
            <span aria-hidden="true" class="urdu-watermark absolute -bottom-8 start-4 text-[7rem] text-white/[0.05]">سہولت</span>
            <div class="animate-blob-slow absolute -bottom-20 -start-20 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl"></div>

            <div class="relative flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-brand-400/40 bg-brand-500/15 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide text-brand-300">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping-ring absolute inline-flex h-full w-full rounded-full bg-brand-400"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-400"></span>
                        </span>
                        {{ __('messages.landing.careers_badge') }}
                    </span>
                    <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ __('messages.landing.careers_title') }}</h2>
                    <p class="mt-3 text-slate-300">{{ __('messages.landing.careers_sub') }}</p>
                </div>
                <a href="{{ route('careers.index') }}"
                   class="btn-shine group relative inline-flex shrink-0 items-center gap-2 rounded-xl bg-brand-500 px-7 py-4 text-base font-bold text-white shadow-xl shadow-brand-900/40 transition hover:scale-105 hover:bg-brand-400">
                    {{ __('messages.landing.careers_btn') }}
                    <svg viewBox="0 0 24 24" class="h-4 w-4 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ FAQ --}}
<section id="faq" class="bg-brand-50/60 py-16 dark:bg-slate-900/40 sm:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mb-12 text-center">
            <div class="mb-3 inline-flex items-center justify-center gap-2">
                <span class="h-0.5 w-5 rounded bg-accent-600"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-accent-600">{{ __('messages.landing.faq_eyebrow') }}</span>
                <span class="h-0.5 w-5 rounded bg-accent-600"></span>
            </div>
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.faq_title') }}</h2>
        </div>

        <div class="space-y-3" x-data="{ open: {{ $faqs->first()?->id ?? 1 }} }">
            @foreach ($faqs as $i => $faq)
                <div class="reveal overflow-hidden rounded-2xl border transition-colors dark:bg-slate-900" style="--reveal-delay: {{ $i * 70 }}ms"
                     :class="open === {{ $faq->id }} ? 'border-brand-400 bg-brand-50/70 dark:border-brand-700' : 'border-slate-200 bg-white hover:border-brand-300 dark:border-slate-800'">
                    <button type="button" @click="open = open === {{ $faq->id }} ? 0 : {{ $faq->id }}"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start"
                            :aria-expanded="open === {{ $faq->id }} ? 'true' : 'false'">
                        <span class="text-sm font-semibold leading-snug text-slate-900 dark:text-white {{ $isUrdu ? 'font-urdu text-base' : '' }}">{{ $faq->question() }}</span>
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full transition-colors"
                              :class="open === {{ $faq->id }} ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'">
                            <svg viewBox="0 0 24 24" class="h-3 w-3 transition-transform duration-300" :class="open === {{ $faq->id }} ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $faq->id }}" x-cloak
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <p class="px-6 pb-5 text-sm leading-relaxed text-slate-600 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-base leading-loose' : '' }}">{{ $faq->answer() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ====================================================== Final CTAs --}}
<section class="pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-5">
            {{-- Consumer CTA --}}
            <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 p-10 lg:col-span-3">
                <span aria-hidden="true" class="urdu-watermark absolute -bottom-8 end-4 text-[7rem] text-white/[0.06]">سہولت</span>
                <div class="animate-blob absolute -top-16 -end-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                <h2 class="relative font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ __('messages.landing.cta_title') }}</h2>
                <p class="relative mt-3 max-w-md text-brand-100">{{ __('messages.landing.cta_sub') }}</p>
                <a href="{{ auth()->check() ? route(auth()->user()->dashboardRoute()) : route('register') }}"
                   class="btn-shine relative mt-7 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-brand-700 shadow-lg transition hover:bg-brand-50">
                    {{ __('messages.landing.cta_btn') }}
                    <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>

            {{-- Provider recruitment CTA --}}
            <div class="reveal relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-10 lg:col-span-2 dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: 120ms">
                <div class="absolute -bottom-12 -end-12 h-44 w-44 rounded-full bg-accent-100/60 blur-2xl dark:bg-accent-500/10"></div>
                <h3 class="relative font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('messages.landing.cta_pro_title') }}</h3>
                <p class="relative mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.landing.cta_pro_sub') }}</p>
                <a href="{{ route('register') }}" class="relative mt-7 inline-flex items-center gap-2 rounded-xl border-2 border-brand-600 px-6 py-3 text-sm font-bold text-brand-700 transition hover:bg-brand-600 hover:text-white dark:text-brand-400 dark:hover:text-white">
                    {{ __('messages.landing.cta_pro_btn') }}
                </a>
            </div>
        </div>
          <div class="mx-auto max-w-7xl mt-5 ">
            <img
            src="/images/AppBanner.jpeg"
            alt="Sahoulat Banner"
            class="w-full object-cover rounded-2xl"
                >
        </div>

        

    </div>
</section>

{{-- ===================================================== Contact form --}}
<section id="contact" class="border-t border-slate-100 bg-white py-16 dark:border-slate-800 dark:bg-slate-950 sm:py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center {{ $isUrdu ? 'font-urdu' : '' }}">
            <h2 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.contact.title') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400 {{ $isUrdu ? 'text-lg' : '' }}">{{ __('messages.contact.subtitle') }}</p>
        </div>

        <div class="reveal mt-10 grid gap-8 lg:grid-cols-3" style="--reveal-delay: 80ms">
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('contact.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="home_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.name') }}</label>
                            <input id="home_name" name="name" type="text" value="{{ old('name') }}" required
                                @error('name') aria-invalid="true" @enderror
                                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                    @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                            <x-field-error name="name" />
                        </div>

                        <div>
                            <label for="home_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.email') }}</label>
                            <input id="home_email" name="email" type="email" value="{{ old('email') }}" required
                                @error('email') aria-invalid="true" @enderror
                                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                    @error('email') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                            <x-field-error name="email" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="home_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.phone_optional') }}</label>
                            <input id="home_phone" name="phone" type="text" value="{{ old('phone') }}"
                                @error('phone') aria-invalid="true" @enderror
                                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                    @error('phone') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                            <x-field-error name="phone" />
                        </div>

                        <div>
                            <label for="home_subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.subject_optional') }}</label>
                            <input id="home_subject" name="subject" type="text" value="{{ old('subject') }}"
                                @error('subject') aria-invalid="true" @enderror
                                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                    @error('subject') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                            <x-field-error name="subject" />
                        </div>
                    </div>

                    <div>
                        <label for="home_message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.message') }}</label>
                        <textarea id="home_message" name="message" rows="5" required
                            @error('message') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                @error('message') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('message') }}</textarea>
                        <x-field-error name="message" />
                    </div>

                    <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 sm:w-auto {{ $isUrdu ? 'font-urdu' : '' }}">
                        {{ __('messages.contact.send') }}
                    </button>
                </form>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_title') }}</h3>
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <p class="text-xs text-slate-400 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_email') }}</p>
                                <a href="mailto:info@sahoulat.com" class="font-medium text-slate-800 transition hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">info@sahoulat.com</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <p class="text-xs text-slate-400 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_phone') }}</p>
                                <a href="https://wa.me/923313578446" class="font-medium text-slate-800 transition hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">+92 331 3578446</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <p class="text-xs text-slate-400 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_address') }}</p>
                                <a href="https://www.google.com/maps/place/Sahoulat/@25.0297021,67.3047431,1010m" class="font-medium text-slate-800 transition hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">Bahria Town Karachi, Pakistan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection