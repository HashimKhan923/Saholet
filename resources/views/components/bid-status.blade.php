@props(['status'])

@php
    $map = [
        'pending'   => ['Pending',   'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',  'bg-amber-500'],
        'accepted'  => ['Accepted',  'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',  'bg-brand-500'],
        'rejected'  => ['Rejected',  'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',          'bg-red-500'],
        'withdrawn' => ['Withdrawn', 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',    'bg-slate-400'],
    ];
    [$label, $classes, $dot] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300', 'bg-slate-400'];
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }} {{ $status === 'pending' ? 'animate-live-pulse' : '' }}"></span>
    {{ $label }}
</span>