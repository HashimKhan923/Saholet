@extends('layouts.app')

@php $displayName = $provider->business_name ?: ($provider->user?->name ?? 'Provider'); @endphp

@section('title', $displayName . ' — ' . config('app.name'))
@section('meta_description', \Illuminate\Support\Str::limit($provider->bio ?? __('messages.providers.subtitle'), 150))

@push('jsonld')
@php
    $providerSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $displayName,
        'description' => $provider->bio,
        'url' => url()->current(),
        'address' => $provider->city ? ['@type' => 'PostalAddress', 'addressLocality' => $provider->city, 'addressCountry' => 'PK'] : null,
    ];
    if ($provider->reviews_count > 0) {
        $providerSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $provider->rating_avg,
            'reviewCount' => (string) $provider->reviews_count,
        ];
    }
    $providerSchema = array_filter($providerSchema, fn ($v) => $v !== null);
@endphp
<script type="application/ld+json">{!! json_encode($providerSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

{{-- Profile header --}}
<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Back link --}}
        <a href="{{ route('providers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-brand-700 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ __('messages.providers.title') }}
        </a>

        <div class="animate-fade-up mt-6 flex flex-col gap-6 sm:flex-row sm:items-center">
            <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-600 font-display text-3xl font-extrabold text-white shadow-lg shadow-brand-600/25">
                {{ mb_substr($displayName, 0, 1) }}
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $displayName }}</h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-700 dark:bg-brand-900 dark:text-brand-300">
                        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('messages.providers.verified_badge') }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-slate-500 dark:text-slate-400">
                    @if ($provider->city)
                        <span class="inline-flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="10" r="3"/><path d="M12 2c4.4 0 8 3.6 8 8 0 5-8 12-8 12S4 15 4 10c0-4.4 3.6-8 8-8z" stroke-linejoin="round"/></svg>
                            {{ $provider->city }}
                        </span>
                    @endif
                    @if ($provider->experience_years)
                        <span>{{ $provider->experience_years }}+ yrs experience</span>
                    @endif
                    @if ($provider->reviews_count > 0)
                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700 dark:text-slate-200">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-amber-400" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                            {{ number_format((float) $provider->rating_avg, 1) }}
                            <span class="font-normal text-slate-400">· {{ $provider->reviews_count }} {{ __('messages.providers.reviews') }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Left column: about + reviews --}}
        <div class="space-y-8 lg:col-span-2">

            {{-- About --}}
            @if ($provider->bio)
                <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.providers.about') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $provider->bio }}</p>
                </div>
            @endif

            {{-- Portfolio --}}
            @if ($provider->portfolioPhotos->isNotEmpty())
                <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: 60ms">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.providers.portfolio_title') }}</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($provider->portfolioPhotos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="group relative aspect-square overflow-hidden rounded-xl">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?: $displayName }}" class="h-full w-full object-cover transition group-hover:scale-105">
                                @if ($photo->caption)
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/80 to-transparent px-2.5 py-2 opacity-0 transition group-hover:opacity-100">
                                        <p class="truncate text-[11px] font-medium text-white">{{ $photo->caption }}</p>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: 80ms">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.providers.reviews') }}</h2>

                @forelse ($reviews as $review)
                    <div class="mt-5 border-t border-slate-100 pt-5 first:mt-4 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ mb_substr($review->consumer?->name ?? 'C', 0, 1) }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $review->consumer?->name ?? 'Customer' }}</p>
                                    <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="flex gap-0.5 text-amber-400" aria-label="{{ $review->rating }} / 5">
                                @for ($s = 1; $s <= 5; $s++)
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 {{ $s <= $review->rating ? '' : 'opacity-25' }}" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                                @endfor
                            </span>
                        </div>
                        @if ($review->comment)
                            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $review->comment }}</p>
                        @endif
                    </div>
                @empty
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.providers.no_reviews_yet') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Right column: services sticky sidebar --}}
        <div>
            <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 lg:sticky lg:top-24 dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: 100ms">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.providers.offered_services') }}</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($provider->providerServices as $ps)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3.5 transition hover:border-brand-200 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:border-brand-800">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $ps->service?->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $ps->service?->category?->name }}</p>
                            </div>
                            <div class="shrink-0 text-end">
                                <p class="text-sm font-bold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $ps->price) }}</p>
                                @auth
                                    @if (auth()->user()->isConsumer() && $ps->service)
                                        <a href="{{ route('consumer.bookings.create', [$provider, $ps->service->slug]) }}" class="text-xs font-semibold text-brand-600 underline-offset-2 hover:underline">{{ __('messages.providers.book_now') }}</a>
                                    @endif
                                @endauth
                                @guest
                                    @if ($ps->service)
                                        <a href="{{ route('services.show', $ps->service->slug) }}" class="text-xs font-semibold text-brand-600 underline-offset-2 hover:underline">{{ __('messages.providers.book_now') }}</a>
                                    @endif
                                @endguest
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('messages.providers.empty') }}</p>
                    @endforelse
                </div>

                @guest
                    <a href="{{ route('register') }}" class="btn-shine mt-6 block rounded-xl bg-brand-600 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        {{ __('messages.nav.get_started') }}
                    </a>
                @endguest
            </div>
        </div>
    </div>
</section>

@endsection