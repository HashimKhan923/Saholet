@extends('layouts.app')

@section('title', $product->name . ' — ' . config('app.name'))
@section('meta_description', $product->description ?: ('Buy ' . $product->name . ' from ' . ($product->providerProfile->business_name ?: 'a Sahoulat provider')))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="grid gap-10 lg:grid-cols-2">
        {{-- Photos --}}
        <div x-data="{ active: 0, photos: @js($product->photos->map(fn ($p) => $p->url())->values()) }">
            <div class="relative aspect-square w-full overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-800">
                <template x-if="photos.length > 0">
                    <img :src="photos[active]" class="h-full w-full object-cover">
                </template>
                <template x-if="photos.length === 0">
                    <div class="flex h-full items-center justify-center text-slate-300 dark:text-slate-700">
                        <svg viewBox="0 0 24 24" class="h-16 w-16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m3 15 4.5-4.5a2 2 0 0 1 2.8 0L15 15M13.5 13.5 15.5 11.5a2 2 0 0 1 2.8 0L21 14.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </template>

                {{-- Wishlist --}}
                <div class="absolute end-3 top-3 z-10">
                    <x-wishlist-button :product="$product" :wishlisted="$wishlisted ?? false" />
                </div>

                {{-- Discount badge --}}
                @if ($product->hasDiscount())
                    <span class="absolute start-3 top-3 z-10 rounded-full bg-brand-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm">
                        {{ $product->discountPercentage() }}% OFF
                    </span>
                @endif

                {{-- Prev/next arrows — only meaningful with more than one photo --}}
                <template x-if="photos.length > 1">
                    <div>
                        <button type="button" @click="active = (active - 1 + photos.length) % photos.length"
                            class="absolute start-3 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md transition hover:bg-white dark:bg-slate-900/90 dark:text-slate-200"
                            aria-label="Previous photo">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" @click="active = (active + 1) % photos.length"
                            class="absolute end-3 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md transition hover:bg-white dark:bg-slate-900/90 dark:text-slate-200"
                            aria-label="Next photo">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="absolute bottom-3 start-1/2 z-10 flex -translate-x-1/2 gap-1.5">
                            <template x-for="(url, i) in photos" :key="'dot-' + i">
                                <button type="button" @click="active = i" class="h-1.5 rounded-full transition-all" :class="active === i ? 'w-5 bg-white' : 'w-1.5 bg-white/60'" :aria-label="'Go to photo ' + (i + 1)"></button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Thumbnail strip --}}
            @if ($product->photos->count() > 1)
                <div class="mt-3 flex gap-2">
                    <template x-for="(url, i) in photos" :key="'thumb-' + i">
                        <button type="button" @click="active = i" class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 transition" :class="active === i ? 'border-brand-500' : 'border-transparent opacity-70 hover:opacity-100'">
                            <img :src="url" class="h-full w-full object-cover">
                        </button>
                    </template>
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $product->name }}</h1>

            <div class="mt-1.5"><x-rating-stars :product="$product" size="base" /></div>

            @if ($product->category)
                <span class="mt-2 inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $product->category->name }}</span>
            @endif

            <a href="{{ route('providers.show', $product->providerProfile) }}" class="mt-3 flex items-center gap-2 text-sm text-slate-500 transition hover:text-brand-700 dark:text-slate-400">
                @if ($product->providerProfile->user?->avatar_url)
                    <img src="{{ $product->providerProfile->user->avatar_url }}" class="h-6 w-6 rounded-lg object-cover">
                @else
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-600 text-[10px] font-bold text-white">{{ mb_substr($product->providerProfile->business_name ?: 'S', 0, 1) }}</span>
                @endif
                Sold by {{ $product->providerProfile->business_name ?: $product->providerProfile->user?->name }}
            </a>

            {{-- Price --}}
            <div class="mt-6 flex items-baseline gap-3">
                @if ($product->hasDiscount())
                    <span class="font-display text-3xl font-extrabold text-brand-600 dark:text-brand-400">Rs. {{ number_format($product->effectivePrice(), 0) }}</span>
                    <span class="text-lg font-medium text-red-600 line-through dark:text-red-400">Rs. {{ number_format((float) $product->price, 0) }}</span>
                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">{{ $product->discountPercentage() }}% off</span>
                @else
                    <span class="font-display text-3xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $product->price, 0) }}</span>
                @endif
            </div>

            <p class="mt-1 text-sm {{ $product->isInStock() ? 'text-slate-500 dark:text-slate-400' : 'font-semibold text-red-600 dark:text-red-400' }}">
                {{ $product->isInStock() ? $product->stock_quantity . ' in stock' : 'Out of stock' }}
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($product->providerProfile->offersDelivery())
                    <span class="inline-flex items-center gap-2 rounded-xl border-2 border-brand-200 bg-brand-50 px-4 py-2.5 text-sm font-bold text-brand-800 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="7" width="15" height="10" rx="1"/><path d="M16 10h3l3 3v4h-6"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                        Delivery available
                    </span>
                @endif
                @if ($product->providerProfile->pickup_enabled)
                    <span class="inline-flex items-center gap-2 rounded-xl border-2 border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-bold text-sky-800 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-300">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-6 9 6v10a1 1 0 0 1-1 1h-4v-6H8v6H4a1 1 0 0 1-1-1V9z" stroke-linejoin="round"/></svg>
                        Self-pickup available
                    </span>
                @endif
            </div>

            <div class="mt-8">
                @auth
                    @if (auth()->user()->isConsumer())
                        <form method="POST" action="{{ route('consumer.cart.items.store') }}" class="flex items-center gap-3" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" {{ $product->isInStock() ? '' : 'disabled' }}
                                class="w-20 rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <button type="submit" :disabled="submitting" {{ $product->isInStock() ? '' : 'disabled' }}
                                class="btn-shine flex-1 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="!submitting">{{ $product->isInStock() ? 'Add to cart' : 'Out of stock' }}</span>
                                <span x-show="submitting" x-cloak>Adding…</span>
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">Only customer accounts can purchase from the shop.</p>
                    @endif
                @else
                    @if ($product->isInStock())
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-shine inline-flex items-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Log in to buy</a>
                    @else
                        <button type="button" disabled class="inline-flex cursor-not-allowed items-center rounded-xl bg-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Out of stock</button>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    {{-- Description — full width below the fold, eBay/Amazon-style --}}
    @if ($product->description)
        <div class="mt-14 border-t-2 border-slate-100 pt-10 dark:border-slate-800">
            <h2 class="font-display text-xl font-bold text-slate-900 dark:text-white">Product description</h2>
            <div class="mt-4 max-w-3xl whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $product->description }}</div>
        </div>
    @endif

    {{-- Reviews --}}
    <div class="mt-14 border-t-2 border-slate-100 pt-10 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <h2 class="font-display text-xl font-bold text-slate-900 dark:text-white">Reviews</h2>
            @if ($product->reviewsCount() > 0)
                <span class="inline-flex items-center gap-1 text-sm font-semibold text-amber-500">
                    ★ {{ number_format($product->ratingAvg(), 1) }}
                    <span class="font-normal text-slate-400">({{ $product->reviewsCount() }})</span>
                </span>
            @endif
        </div>
        @if ($product->reviews->isEmpty())
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No reviews yet — be the first to buy and rate this product.</p>
        @else
            <ul class="mt-5 max-w-3xl space-y-5">
                @foreach ($product->reviews as $review)
                    <li class="border-b border-slate-100 pb-5 last:border-0 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $review->user?->name ?? 'Verified buyer' }}</p>
                            <span class="text-sm font-bold text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $review->comment }}</p>
                        @endif
                        <p class="mt-1.5 text-xs text-slate-400">{{ $review->created_at->format('d M Y') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($related->isNotEmpty())
        <div class="mt-14 border-t-2 border-slate-100 pt-10 dark:border-slate-800">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">More from this shop</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)
                    <a href="{{ route('shop.show', $item) }}" class="card-lift group overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <div class="aspect-square w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if ($item->photos->isNotEmpty())
                                <img src="{{ $item->photos->first()->url() }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $item->name }}</p>
                            @if ($item->hasDiscount())
                                <p class="text-sm"><span class="font-bold text-brand-600 dark:text-brand-400">Rs. {{ number_format($item->effectivePrice(), 0) }}</span> <span class="text-xs text-red-600 line-through dark:text-red-400">Rs. {{ number_format((float) $item->price, 0) }}</span></p>
                            @else
                                <p class="text-sm font-bold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $item->price, 0) }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
