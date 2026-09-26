@props(['category', 'size' => 'sm'])

@php
    $isLg = $size === 'lg';
    $titleClass = $isLg
        ? 'font-display text-xl font-bold leading-tight tracking-tight text-slate-900 dark:text-white sm:text-3xl'
        : 'font-display text-xl font-bold tracking-tight text-slate-900 dark:text-white';
    $iconBoxClass = $isLg ? 'h-11 w-11 sm:h-14 sm:w-14' : 'h-11 w-11';
    $iconClass = $isLg ? 'h-6 w-6 sm:h-7 sm:w-7' : 'h-6 w-6';
    $titleTag = $isLg ? 'h1' : 'h2';
@endphp

<div class="flex items-center gap-3 sm:items-start sm:gap-3.5">
    <span class="flex {{ $iconBoxClass }} shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
        <x-service-icon :name="$category->icon" class="{{ $iconClass }}" />
    </span>
    <div>
        <{{ $titleTag }} class="{{ $titleClass }}">{{ $category->name }}</{{ $titleTag }}>
        @if ($category->description)
            <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400 sm:mt-1 sm:line-clamp-none {{ $isLg ? 'sm:max-w-2xl' : '' }} sm:text-sm">{{ $category->description }}</p>
        @endif
    </div>
</div>
