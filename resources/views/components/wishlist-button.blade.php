@props(['product', 'wishlisted' => false, 'size' => 'md'])

@php
    $dims = $size === 'sm' ? 'h-8 w-8' : 'h-10 w-10';
    $iconDims = $size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
@endphp

@auth
    @if (auth()->user()->isConsumer())
        <form method="POST" action="{{ route('consumer.wishlist.toggle') }}" onclick="event.stopPropagation(); event.preventDefault(); this.submit();">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button type="submit"
                class="flex {{ $dims }} shrink-0 items-center justify-center rounded-full shadow-sm ring-1 ring-slate-200 transition dark:ring-slate-700
                    {{ $wishlisted ? 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400' : 'bg-white text-slate-400 hover:text-red-500 dark:bg-slate-900 dark:text-slate-500 dark:hover:text-red-400' }}"
                aria-label="{{ $wishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}" aria-pressed="{{ $wishlisted ? 'true' : 'false' }}">
                <svg viewBox="0 0 24 24" class="{{ $iconDims }}" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                    <path d="M12 20.5s-7.5-4.6-10-9.3C.4 7.8 2 4 6 4c2.2 0 3.7 1.2 6 3.5C14.3 5.2 15.8 4 18 4c4 0 5.6 3.8 4 7.2-2.5 4.7-10 9.3-10 9.3Z" stroke-linejoin="round"/>
                </svg>
            </button>
        </form>
    @endif
@endauth
