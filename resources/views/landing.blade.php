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
<section class="relative overflow-hidden">
    {{-- Ambient backdrop: dotted grid + drifting brand blobs --}}
    <div class="absolute inset-0 -z-20 bg-gradient-to-b from-brand-50 via-white to-slate-50 dark:from-brand-950 dark:via-slate-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-60"></div>
    <div class="animate-blob absolute -top-28 end-[-4rem] -z-10 h-96 w-96 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-500/10"></div>
    <div class="animate-blob-slow absolute -bottom-32 start-[-6rem] -z-10 h-80 w-80 rounded-full bg-accent-200/40 blur-3xl dark:bg-accent-500/10"></div>

    {{-- Giant Urdu wordmark watermark --}}
    <span aria-hidden="true" class="urdu-watermark absolute -bottom-10 end-4 -z-10 text-[9rem] font-bold text-brand-900/[0.04] dark:text-brand-100/[0.04] sm:text-[13rem]">سہولت</span>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 py-16 sm:py-20 lg:grid-cols-2 lg:py-28">

            <div class="animate-fade-up">
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-brand-700 shadow-sm backdrop-blur dark:border-brand-800 dark:bg-slate-900/80 dark:text-brand-400">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping-ring absolute inline-flex h-full w-full rounded-full bg-brand-400"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
                    </span>
                    {{ __('messages.hero.badge') }}
                </span>

                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.12] tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-[3.4rem]">
                    {{ __('messages.hero.title') }}
                    {{-- Calligraphic red swash, echoing the logo's brushstroke --}}
                    <svg aria-hidden="true" viewBox="0 0 300 24" class="mt-1 h-5 w-56 sm:w-72" fill="none">
                        <path class="swash-path" d="M4 16 C 60 4, 150 2, 214 10 C 250 14, 280 12, 296 8 M 60 20 C 120 13, 200 13, 262 17"
                            stroke="var(--color-accent-500)" stroke-width="5" stroke-linecap="round"/>
                    </svg>
                </h1>

                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                    {{ __('messages.hero.subtitle') }}
                </p>

                {{-- Hero search — the primary action --}}
                <form action="{{ route('services.index') }}" method="GET" class="mt-8 max-w-xl">
                    <div class="flex items-stretch gap-2 rounded-2xl border border-slate-200 bg-white/90 p-2 shadow-lg shadow-brand-900/5 backdrop-blur transition focus-within:border-brand-400 focus-within:ring-4 focus-within:ring-brand-100 dark:border-slate-700 dark:bg-slate-900/90 dark:focus-within:ring-brand-900/50">
                        <svg viewBox="0 0 24 24" class="ms-2 h-5 w-5 self-center text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                        <input type="search" name="q" placeholder="{{ __('messages.landing.search_placeholder') }}"
                            class="min-w-0 flex-1 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-white">
                        <button type="submit" class="btn-shine shrink-0 rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            {{ __('messages.landing.search_btn') }}
                        </button>
                    </div>
                </form>

                <div class="mt-8 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.5 11 14.5 15.5 10" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
                        {{ __('messages.hero.verified') }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3z" stroke-linejoin="round"/></svg>
                        {{ __('messages.hero.secure') }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg>
                        {{ __('messages.hero.tracking') }}
                    <span>
                </div>
            </div>

            {{-- hero section image with floating trust chips --}}
           <div class="animate-fade-up-delayed relative mx-auto w-full max-w-lg lg:max-w-none">

    {{-- Hero Image --}}
    <img
        src="/images/Hero.png"
        alt="Sahoulat Hero"
        class="animate-float object-cover relative z-10 w-full h-auto select-none"
    >

   
  {{-- Floating chips --}} <div class="animate-float-slow absolute -start-4 -top-5 hidden items-center gap-2 rounded-2xl border border-slate-200/80 bg-white/95 px-3.5 py-2.5 shadow-lg backdrop-blur sm:flex dark:border-slate-800 dark:bg-slate-900/95">
     <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white"> <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg> </span> <div class="text-xs"> <p class="font-bold text-slate-900 dark:text-white">KYC verified</p> <p class="text-slate-500 dark:text-slate-400">CNIC checked</p> </div> </div> <div class="z-10 animate-float absolute -bottom-5 -end-3 hidden items-center gap-2 rounded-2xl border border-slate-200/80 bg-white/95 px-3.5 py-2.5 shadow-lg backdrop-blur sm:flex dark:border-slate-800 dark:bg-slate-900/95" style="animation-delay: 1.4s;"> <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent-500/10 text-accent-600"> <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg> </span> <div class="text-xs"> <p class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['rating'], 1) }} / 5</p> <p class="text-slate-500 dark:text-slate-400">{{ __('messages.landing.stats_rating') }}</p> </div> </div> </div>

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
    
    'Federal B Area',
    'Buffer Zone',
    'Malir',
    
    'Shah Faisal Colony',
    
    'Orangi Town',
    'Garden',
    
    'Defence View',
    'Karsaz',
    
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
        <div class="reveal max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600">{{ __('messages.nav.services') }}</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.services_title') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">{{ __('messages.landing.services_sub') }}</p>
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
        <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($categories as $i => $category)
                @php $iconTone = $categoryPalette[$category->icon] ?? $categoryPalette['default']; @endphp
                <a href="{{ route('services.index') }}"
                   class="reveal card-lift group relative isolate block aspect-[4/5] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-800"
                   style="--reveal-delay: {{ ($i % 4) * 70 }}ms">
                    @if ($category->image_url)
                       <img src="{{ $category->image_url }}" alt="" loading="lazy"
                             class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/85 via-20% to-transparent dark:from-slate-950 dark:via-slate-950/85"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-50 to-white dark:from-slate-900 dark:to-slate-800"></div>
                    @endif

                    <span class="absolute right-3 top-3 z-10 flex h-8 w-8 -translate-y-1 items-center justify-center rounded-full bg-white/90 text-slate-500 opacity-0 shadow-sm backdrop-blur transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 dark:bg-slate-900/90 dark:text-slate-300">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:-scale-x-100" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M8 7h9v9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>

                    <div class="absolute inset-x-0 bottom-0 z-10 flex flex-col gap-3 p-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl shadow-sm transition duration-300 group-hover:scale-105 {{ $iconTone }}">
                            <x-service-icon :name="$category->icon" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-display text-base font-bold text-slate-900 transition group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $category->name }}</p>
                            <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
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
            <a href="{{ route('services.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                {{ __('messages.landing.browse_all') }}
                <svg viewBox="0 0 24 24" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ================================================= Main Banner --}}
<section class="">
    <div class="mx-auto max-w-7xl px-4 ">
         <img
        src="/images/HeroBanner.jpeg"
        alt="Sahoulat Banner"
        class="w-full object-cover rounded-2xl"
    >

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
                <a href="{{ route('services.index') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
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
                <a href="{{ auth()->check() && auth()->user()->isConsumer() ? route('consumer.jobs.create') : route('register') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
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
                <a href="{{ auth()->check() && auth()->user()->isConsumer() ? route('consumer.contracts.create') : route('register') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-400">
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
                <a href="{{ auth()->check() && auth()->user()->isConsumer() ? route('consumer.emergencies.create') : route('register') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-accent-700 hover:text-accent-800 dark:text-accent-400">
                    {{ __('messages.landing.flow_sos_cta') }} <span aria-hidden="true" class="rtl:hidden">→</span><span aria-hidden="true" class="hidden rtl:inline">←</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ==================================================== How it works --}}
<section id="how" class="relative overflow-hidden py-16 sm:py-24">
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-40"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600">{{ __('messages.nav.how') }}</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.how_title') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">{{ __('messages.landing.how_sub') }}</p>
        </div>

        @php
            $steps = [
                ['n' => '1', 'title' => __('messages.landing.step1_title'), 'desc' => __('messages.landing.step1_desc')],
                ['n' => '2', 'title' => __('messages.landing.step2_title'), 'desc' => __('messages.landing.step2_desc')],
                ['n' => '3', 'title' => __('messages.landing.step3_title'), 'desc' => __('messages.landing.step3_desc')],
            ];
        @endphp

        <div class="relative mt-12 grid gap-6 md:grid-cols-3">
            {{-- Dashed connector line (desktop) --}}
            <div aria-hidden="true" class="absolute inset-x-16 top-11 hidden border-t-2 border-dashed border-brand-200 md:block dark:border-brand-900"></div>

            @foreach ($steps as $i => $step)
                <div class="reveal relative rounded-3xl border border-slate-200 bg-white/90 p-7 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90" style="--reveal-delay: {{ $i * 110 }}ms">
                    <span class="relative z-10 flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-600 font-display text-base font-extrabold text-white shadow-md shadow-brand-600/30">{{ $step['n'] }}</span>
                    <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================== Subscription plans band --}}
<section class="border-t border-slate-100 py-16 dark:border-slate-800 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-800 p-10 sm:p-14">
            <div class="animate-blob-slow absolute -top-20 -end-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

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
                   class="btn-shine relative inline-flex shrink-0 items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-brand-800 shadow-lg transition hover:bg-brand-50">
                    Browse plans
                    <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
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
        @endphp

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($cards as $i => $card)
                <figure class="reveal card-lift flex flex-col rounded-3xl border border-slate-200 bg-slate-50/70 p-7 dark:border-slate-800 dark:bg-slate-900/70" style="--reveal-delay: {{ $i * 100 }}ms">
                    <div class="flex gap-0.5 text-amber-400" aria-label="5 out of 5 stars">
                        @for ($s = 0; $s < 5; $s++)
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                        @endfor
                    </div>
                    <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-300">“{{ $card['comment'] }}”</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">{{ mb_substr($card['name'], 0, 1) }}</span>
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
        <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-10 sm:p-14 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
            <span aria-hidden="true" class="urdu-watermark absolute -bottom-8 start-4 text-[7rem] text-white/[0.05]">سہولت</span>
            <div class="animate-blob-slow absolute -bottom-20 -start-20 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl"></div>

            <div class="relative flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-white/90">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('messages.nav.careers') }}
                    </span>
                    <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ __('messages.landing.careers_title') }}</h2>
                    <p class="mt-3 text-slate-300">{{ __('messages.landing.careers_sub') }}</p>
                </div>
                <a href="{{ route('careers.index') }}"
                   class="btn-shine relative inline-flex shrink-0 items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-slate-900 shadow-lg transition hover:bg-brand-50">
                    {{ __('messages.landing.careers_btn') }}
                    <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ FAQ --}}
<section class="py-16 sm:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="reveal text-center font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.landing.faq_title') }}</h2>

        <div class="mt-10 space-y-3" x-data="{ open: 1 }">
            @foreach ([1, 2, 3, 4,5,6,7,8] as $i)
                <div class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white transition dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: {{ ($i - 1) * 70 }}ms"
                     :class="open === {{ $i }} ? 'border-brand-300 shadow-md shadow-brand-900/5 dark:border-brand-800' : ''">
                    <button type="button" @click="open = open === {{ $i }} ? 0 : {{ $i }}"
                            class="flex w-full items-center justify-between gap-4 px-6 py-4 text-start"
                            :aria-expanded="open === {{ $i }} ? 'true' : 'false'">
                        <span class="py-1 text-sm font-semibold text-slate-900 dark:text-white">{{ __('messages.landing.faq_q' . $i) }}</span>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0 text-brand-600 transition-transform duration-300" :class="open === {{ $i }} ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <p class="px-6 pb-5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('messages.landing.faq_a' . $i) }}</p>
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
          <div class="mx-auto max-w-7xl my-4 ">
            <img
            src="/images/AppBanner.jpeg"
            alt="Sahoulat Banner"
            class="w-full object-cover rounded-2xl"
                >
        </div>

        

    </div>
</section>

@endsection