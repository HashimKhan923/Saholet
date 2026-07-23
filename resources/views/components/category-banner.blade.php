@props(['category', 'size' => 'sm'])

@php
    $isLg = $size === 'lg';
    $aspect = $isLg ? 'aspect-[21/9] sm:aspect-[3/1]' : 'aspect-[3/1] sm:aspect-[4/1]';
    $titleClass = $isLg
        ? 'font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl'
        : 'font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white';
    $iconBoxClass = $isLg ? 'h-14 w-14' : 'h-11 w-11';
    $iconClass = $isLg ? 'h-7 w-7' : 'h-6 w-6';
    $titleTag = $isLg ? 'h1' : 'h2';
@endphp

<div>
    @if ($category->banner_url)
        <div class="{{ $aspect }} w-full overflow-hidden rounded-2xl {{ $isLg ? 'sm:rounded-3xl' : '' }} bg-slate-100 dark:bg-slate-800">
            <img src="{{ $category->banner_url }}" alt="{{ $category->name }}" loading="lazy" class="h-full w-full object-cover">
        </div>
    @endif

    <div class="{{ $category->banner_url ? 'mt-5' : '' }} flex items-start gap-3.5">
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
</div>
