@props(['product', 'size' => 'sm'])

@php
    $count = $product->reviewsCount();
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
@endphp

@if ($count > 0)
    @php $rounded = (int) round($product->ratingAvg()); @endphp
    <div class="flex items-center gap-1">
        <span class="{{ $textSize }} tracking-tight text-amber-500">{{ str_repeat('★', $rounded) }}{{ str_repeat('☆', 5 - $rounded) }}</span>
        <span class="{{ $textSize }} text-slate-500 dark:text-slate-400">({{ $count }})</span>
    </div>
@endif
