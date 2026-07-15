@props(['status'])

@php
    $map = [
        'open' => ['Open', 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'],
        'resolved' => ['Resolved', 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400'],
        'dismissed' => ['Dismissed', 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>