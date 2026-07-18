@extends('layouts.app')

@section('title', __('messages.providers.title') . ' — ' . config('app.name'))
@section('meta_description', __('messages.providers.subtitle'))

@section('content')

<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <img src="{{ asset('images/Professionals.jpeg') }}" alt="Sahoulat professionals" class="animate-fade-up mb-8 w-full rounded-2xl md:object-cover object-contain shadow-sm md:h-100 h-32" loading="eager">
        <h1 class="animate-fade-up font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ __('messages.providers.title') }}</h1>
        <p class="animate-fade-up mt-3 max-w-2xl text-slate-600 dark:text-slate-400">{{ __('messages.providers.subtitle') }}</p>

        {{-- Search + city filter --}}
        <form method="GET" action="{{ route('providers.index') }}" class="animate-fade-up-delayed mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('messages.providers.search_placeholder') }}"
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pe-3.5 ps-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <select name="city" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                <option value="">{{ __('messages.providers.all_cities') }}</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-shine rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                {{ __('messages.landing.search_btn') }}
            </button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($providers->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
            <svg viewBox="0 0 24 24" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.providers.empty') }}</p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($providers as $i => $provider)
                <a href="{{ route('providers.show', $provider) }}"
                   class="reveal card-lift group flex flex-col rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800"
                   style="--reveal-delay: {{ ($i % 3) * 80 }}ms">

                    {{-- Top row: avatar + name + badge --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 font-display text-base font-extrabold text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-900 dark:text-brand-300">
                                {{ mb_substr($provider->business_name ?: ($provider->user?->name ?? 'S'), 0, 1) }}
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">
                                    {{ $provider->business_name ?: $provider->user?->name }}
                                </h2>
                                @if ($provider->city)
                                    <p class="mt-0.5 inline-flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="10" r="3"/><path d="M12 2c4.4 0 8 3.6 8 8 0 5-8 12-8 12S4 15 4 10c0-4.4 3.6-8 8-8z" stroke-linejoin="round"/></svg>
                                        {{ $provider->city }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-brand-50 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-700 dark:bg-brand-950/60 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('messages.providers.verified_badge') }}
                        </span>
                    </div>

                    {{-- Bio excerpt --}}
                    @if ($provider->bio)
                        <p class="mt-4 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $provider->bio }}</p>
                    @endif

                    {{-- Service skill chips --}}
                    @if ($provider->providerServices->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach ($provider->providerServices->take(3) as $ps)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $ps->service?->name }}</span>
                            @endforeach
                            @if ($provider->providerServices->count() > 3)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">+{{ $provider->providerServices->count() - 3 }}</span>
                            @endif
                        </div>
                    @endif

                    {{-- Footer: rating + view link --}}
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4 mt-5 dark:border-slate-800">
                        @if ($provider->reviews_count > 0)
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-amber-400" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                                {{ number_format((float) $provider->rating_avg, 1) }}
                                <span class="font-normal text-slate-400">({{ $provider->reviews_count }})</span>
                            </span>
                        @else
                            <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold text-brand-700 dark:bg-brand-950/60 dark:text-brand-400">{{ __('messages.providers.no_reviews') }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 dark:text-brand-400">
                            {{ __('messages.providers.view_profile') }}
                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $providers->links() }}
        </div>
    @endif
</section>

@endsection