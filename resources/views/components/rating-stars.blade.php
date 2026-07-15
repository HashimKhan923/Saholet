@props(['rating' => 0, 'count' => null])

@php
    $value = (float) $rating;
    $filled = (int) round($value);
@endphp

<span class="inline-flex items-center gap-1.5">
    <span class="inline-flex">
        @for ($i = 1; $i <= 5; $i++)
            <svg viewBox="0 0 20 20" class="h-4 w-4 {{ $i <= $filled ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor">
                <path d="M10 1.6l2.5 5.1 5.6.8-4 3.9 1 5.6L10 14.4 5 17l1-5.6-4-3.9 5.6-.8L10 1.6z"/>
            </svg>
        @endfor
    </span>
    @if ($value > 0)
        <span class="text-xs font-semibold text-slate-700">{{ number_format($value, 1) }}</span>
    @endif
    @if (! is_null($count))
        <span class="text-xs text-slate-400">({{ $count }})</span>
    @endif
</span>