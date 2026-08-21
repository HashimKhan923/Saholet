@props(['url' => null, 'name' => '', 'size' => 'md'])

@php
    $dimensions = match ($size) {
        'sm' => 'h-8 w-8',
        'lg' => 'h-14 w-14',
        default => 'h-10 w-10',
    };
    $iconSize = match ($size) {
        'sm' => 'h-4 w-4',
        'lg' => 'h-7 w-7',
        default => 'h-5 w-5',
    };
@endphp

@if ($url)
    <img src="{{ $url }}" alt="{{ $name }}" class="{{ $dimensions }} shrink-0 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
@else
    <span class="inline-flex {{ $dimensions }} shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:ring-slate-700">
        <svg viewBox="0 0 24 24" class="{{ $iconSize }}" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg>
    </span>
@endif
