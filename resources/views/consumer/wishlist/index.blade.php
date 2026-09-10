@extends('layouts.app')

@section('title', 'My wishlist — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My wishlist</h1>

    @if ($products->isEmpty())
        <div class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center dark:border-slate-700 dark:bg-slate-900">
            <svg viewBox="0 0 24 24" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20.5s-7.5-4.6-10-9.3C.4 7.8 2 4 6 4c2.2 0 3.7 1.2 6 3.5C14.3 5.2 15.8 4 18 4c4 0 5.6 3.8 4 7.2-2.5 4.7-10 9.3-10 9.3Z" stroke-linejoin="round"/></svg>
            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Nothing wishlisted yet.</p>
            <a href="{{ route('shop.index') }}" class="mt-5 inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Browse products</a>
        </div>
    @else
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($products as $product)
                <a href="{{ route('shop.show', $product) }}"
                   class="card-lift group relative flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                    <div class="absolute end-3 top-3 z-10">
                        <x-wishlist-button :product="$product" :wishlisted="true" size="sm" />
                    </div>
                    @if ($product->hasDiscount())
                        <span class="absolute start-3 top-3 z-10 rounded-full bg-brand-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">{{ $product->discountPercentage() }}% OFF</span>
                    @endif
                    <div class="aspect-square w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if ($product->photos->isNotEmpty())
                            <img src="{{ $product->photos->first()->url() }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="font-display text-sm font-bold text-slate-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $product->name }}</h3>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $product->providerProfile->business_name ?: $product->providerProfile->user?->name }}</p>
                        <div class="mt-auto pt-3">
                            @if ($product->hasDiscount())
                                <span class="flex items-baseline gap-1.5">
                                    <span class="font-display text-base font-extrabold text-brand-600 dark:text-brand-400">Rs. {{ number_format($product->effectivePrice(), 0) }}</span>
                                    <span class="text-xs text-red-600 line-through dark:text-red-400">Rs. {{ number_format((float) $product->price, 0) }}</span>
                                </span>
                            @else
                                <span class="font-display text-base font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $product->price, 0) }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</section>
@endsection
