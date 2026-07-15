@extends('layouts.app')

@section('title', $service->name . ' — ' . config('app.name'))
@section('meta_description', \Illuminate\Support\Str::limit($service->description ?: ($service->name . ' — starting from Rs. ' . number_format($service->base_price, 0) . '. Verified professionals across Pakistan.'), 155))

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $service->name,
    'description' => $service->description ?: ($service->name . ' service, provided by verified professionals.'),
    'serviceType' => $service->category->name,
    'provider' => ['@type' => 'LocalBusiness', 'name' => config('app.name'), 'url' => url('/')],
    'areaServed' => 'Pakistan',
    'offers' => [
        '@type' => 'Offer',
        'price' => (string) $service->base_price,
        'priceCurrency' => 'PKR',
        'availability' => 'https://schema.org/InStock',
        'url' => url()->current(),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('services.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400">Services</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="font-medium text-slate-700 dark:text-slate-200">{{ $service->category->name }}</span>
    </nav>

    <div class="mt-6 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <x-service-icon :name="$service->category->icon" class="h-7 w-7" />
                </span>
                <div>
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $service->category->name }}</span>
                    <h1 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">{{ $service->name }}</h1>
                </div>
            </div>

            @if ($service->description)
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">About this service</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $service->description }}</p>
                </div>
            @endif

            <div class="mt-8">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Available providers</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($providers as $offering)
                        @php $p = $offering->providerProfile; @endphp
                        <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 font-display text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ strtoupper(substr($p->business_name ?: $p->user->name, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $p->business_name ?: $p->user->name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $p->city ?: 'Pakistan' }} · {{ $p->experience_years }} yr experience</p>
                                    <div class="mt-1.5">
                                        @if ($p->reviews_count > 0)
                                            <x-rating-stars :rating="$p->rating_avg" :count="$p->reviews_count" />
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-slate-500">New provider</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <span class="text-sm font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($offering->price, 0) }}</span>
                                <a href="{{ route('consumer.bookings.create', ['provider' => $p->id, 'service' => $service->slug]) }}"
                                   class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                                    Book
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No providers available for this service yet.</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Please check back soon.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $providers->links() }}</div>
            </div>

            @if ($related->isNotEmpty())
                <div class="mt-8">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">More in {{ $service->category->name }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($related as $item)
                            <a href="{{ route('services.show', $item) }}"
                               class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                                <p class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $item->name }}</p>
                                <p class="mt-1 text-sm font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($item->base_price, 0) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-baseline justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Starting from</span>
                    <span class="font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($service->base_price, 0) }}</span>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 text-sm dark:bg-slate-800">
                    <span class="text-slate-500 dark:text-slate-400">Est. duration</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">~ {{ $service->duration_minutes }} min</span>
                </div>
                <p class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">Choose a provider to pick a time and book.</p>
            </div>
        </aside>
    </div>
</section>
@endsection