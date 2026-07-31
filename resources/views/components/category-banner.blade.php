@props(['category', 'size' => 'sm'])

@php
    $isLg = $size === 'lg';
    $titleClass = $isLg
        ? 'font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl'
        : 'font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white';
    $iconBoxClass = $isLg ? 'h-14 w-14' : 'h-11 w-11';
    $iconClass = $isLg ? 'h-7 w-7' : 'h-6 w-6';
    $titleTag = $isLg ? 'h1' : 'h2';
@endphp

<div class="flex items-start gap-3.5">
    <span class="flex {{ $iconBoxClass }} shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
        <x-service-icon :name="$category->icon" class="{{ $iconClass }}" />
    </span>
    <div>
        <{{ $titleTag }} class="{{ $titleClass }}">{{ $category->name }}</{{ $titleTag }}>
        @if ($category->description)
            <p class="mt-1 {{ $isLg ? 'max-w-2xl text-sm' : 'text-sm' }} text-slate-500 dark:text-slate-400">{{ $category->description }}</p>
        @endif
    </div>
</div>
