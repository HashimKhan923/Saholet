@props(['status'])

@php
    $map = [
        'open' => ['Open', 'bg-sky-50 text-sky-700'],
        'awarded' => ['Awarded', 'bg-brand-50 text-brand-700'],
        'cancelled' => ['Cancelled', 'bg-red-50 text-red-700'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>