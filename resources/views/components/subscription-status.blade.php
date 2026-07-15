@props(['status'])

@php
    $map = [
        'pending_assignment' => ['Awaiting provider', 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'],
        'active' => ['Active', 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400'],
        'completed' => ['Completed', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'],
        'cancelled' => ['Cancelled', 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>
