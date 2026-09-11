@props([
    'product',
    'wishlisted' => false,
])

<a href="{{ route('shop.show', $product) }}"
   class="card-lift group relative flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
    <div class="absolute end-3 top-3 z-10">
        <x-wishlist-button :product="$product" :wishlisted="$wishlisted" size="sm" />
    </div>
    @if ($product->hasDiscount())
        <span class="absolute start-3 top-3 z-10 rounded-full bg-brand-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">{{ $product->discountPercentage() }}% OFF</span>
    @endif
    <div class="aspect-square w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
        @if ($product->photos->isNotEmpty())
            <img src="{{ $product->photos->first()->url() }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-slate-300 dark:text-slate-700">
                <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m3 15 4.5-4.5a2 2 0 0 1 2.8 0L15 15M13.5 13.5 15.5 11.5a2 2 0 0 1 2.8 0L21 14.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="font-display text-sm font-bold text-slate-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $product->name }}</h3>
        <p class="mt-0.5 text-xs text-slate-400">{{ $product->providerProfile->shopName() }}</p>
        <div class="mt-auto flex items-center justify-between pt-3">
            @if ($product->hasDiscount())
                <span class="flex items-baseline gap-1.5">
                    <span class="font-display text-base font-extrabold text-brand-600 dark:text-brand-400">Rs. {{ number_format($product->effectivePrice(), 0) }}</span>
                    <span class="text-xs text-red-600 line-through dark:text-red-400">Rs. {{ number_format((float) $product->price, 0) }}</span>
                </span>
            @else
                <span class="font-display text-base font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $product->price, 0) }}</span>
            @endif
            @unless ($product->isInStock())
                <span class="text-[11px] font-semibold text-red-600 dark:text-red-400">Out of stock</span>
            @endunless
        </div>
    </div>
</a>
