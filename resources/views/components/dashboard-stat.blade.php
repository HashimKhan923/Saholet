@props([
    'label' => '',
    'value' => '',
    'href' => null,
    'badge' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

{{--
    Icon slot content must size itself via `class="h-full w-full"` (not a fixed
    h-*/w-* utility) — it's rendered twice, once inside the small badge and once
    inside the large watermark, and needs to fill whichever wrapper it's in.
--}}
<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-s-4 border-slate-100 border-s-brand-500 bg-gradient-to-br from-white to-brand-50/70 p-4 shadow-sm transition dark:border-slate-800 dark:border-s-brand-500 dark:from-slate-800/60 dark:to-slate-800/60{{ $href ? ' hover:-translate-y-0.5 hover:shadow-md' : '' }}">
    <span class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 p-3 text-white shadow-sm">
        {{ $slot }}
    </span>
    <div class="relative z-10">
        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-400">
            {{ $label }}
            @if ($badge)
                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold normal-case tracking-normal text-white shadow-sm">{{ $badge }}</span>
            @endif
        </p>
        <p class="mt-0.5 font-display text-2xl font-extrabold text-slate-900 dark:text-white">{{ $value }}</p>
    </div>
    <span class="pointer-events-none absolute -bottom-4 -end-4 h-20 w-20 p-4 text-brand-100 dark:text-brand-900/40">
        {{ $slot }}
    </span>
</{{ $tag }}>
