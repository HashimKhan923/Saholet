{{--
    Two calling conventions, both supported:
      - :product="$product"          — aggregate rating for a product, via ratingAvg()/reviewsCount()
      - :rating="$x" :count="$y"?    — raw scalars (a provider's aggregate, or a single review's rating
                                        with no count at all, e.g. one booking's review stars)
--}}
@props(['product' => null, 'rating' => null, 'count' => null, 'size' => 'sm'])

@php
    if ($product) {
        $ratingValue = $product->ratingAvg();
        $countValue = $product->reviewsCount();
    } else {
        $ratingValue = $rating;
        $countValue = $count;
    }
    $showCount = $countValue !== null;
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
@endphp

@if ($ratingValue !== null && (float) $ratingValue > 0 && (! $showCount || $countValue > 0))
    @php $rounded = (int) round((float) $ratingValue); @endphp
    <div class="flex items-center gap-1">
        <span class="{{ $textSize }} tracking-tight text-amber-500">{{ str_repeat('★', $rounded) }}{{ str_repeat('☆', 5 - $rounded) }}</span>
        @if ($showCount)
            <span class="{{ $textSize }} text-slate-500 dark:text-slate-400">({{ $countValue }})</span>
        @endif
    </div>
@endif
