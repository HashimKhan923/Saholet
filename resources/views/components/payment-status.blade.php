@props(['status'])

@php
    $map = [
        'pending' => ['Payment pending', 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'],
        'escrow' => ['In escrow', 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400'],
        'released' => ['Released', 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400'],
        'refunded' => ['Refunded', 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'],
        'failed' => ['Failed', 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>