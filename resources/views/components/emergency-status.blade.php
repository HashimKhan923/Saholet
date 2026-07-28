@props(['status'])

@php
    $map = [
        'open' => ['Awaiting quote', 'bg-amber-50 text-amber-700'],
        'quoted' => ['Quote ready', 'bg-sky-50 text-sky-700'],
        'accepted' => ['Finding a provider…', 'bg-violet-50 text-violet-700'],
        'declined' => ['Quote declined', 'bg-slate-100 text-slate-500'],
        'matched' => ['Matched', 'bg-brand-50 text-brand-700'],
        'cancelled' => ['Cancelled', 'bg-slate-100 text-slate-500'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>