@props(['status'])

@php
    $map = [
        'open' => ['Searching…', 'bg-amber-50 text-amber-700'],
        'matched' => ['Matched', 'bg-brand-50 text-brand-700'],
        'cancelled' => ['Cancelled', 'bg-slate-100 text-slate-500'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>