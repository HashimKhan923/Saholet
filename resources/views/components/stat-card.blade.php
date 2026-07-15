@props([
    'label'    => '',
    'value'    => 0,
    'prefix'   => '',
    'suffix'   => '',
    'decimals' => 0,
    'href'     => null,
    'hint'     => null,
    'delta'    => null,
    'tone'     => 'brand',
])

@php
    $tones = [
        'brand'  => ['from-brand-500 to-brand-700', 'bg-brand-500/10'],
        'amber'  => ['from-amber-400 to-amber-600', 'bg-amber-500/10'],
        'sky'    => ['from-sky-400 to-sky-600', 'bg-sky-500/10'],
        'violet' => ['from-violet-400 to-violet-600', 'bg-violet-500/10'],
        'red'    => ['from-red-400 to-red-600', 'bg-red-500/10'],
    ];
    [$iconGradient, $glowTone] = $tones[$tone] ?? $tones['brand'];
    $tag = $href ? 'a' : 'div';
    $up = ! is_null($delta) && $delta >= 0;
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    class="card-lift group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.03] dark:border-slate-800 dark:bg-slate-900">

    <div class="pointer-events-none absolute -end-8 -top-8 h-28 w-28 rounded-full {{ $glowTone }} blur-2xl transition duration-500 group-hover:scale-125"></div>

    <div class="relative flex items-start justify-between gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $iconGradient }} text-white shadow-md shadow-slate-900/10 transition duration-300 group-hover:scale-105 group-hover:-rotate-3">
            {{ $slot }}
        </span>

        @if (! is_null($delta))
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold
                {{ $up ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-400'
                       : 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400' }}">
                <svg viewBox="0 0 24 24" class="h-3 w-3 {{ $up ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ abs($delta) }}%
            </span>
        @endif
    </div>

    <p class="relative mt-4 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ $label }}</p>

    <p class="relative mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
        @if ($prefix)<span class="text-base font-bold text-slate-400">{{ $prefix }}</span>@endif<span
            x-data="{
                n: 0,
                target: {{ (float) $value }},
                dec: {{ (int) $decimals }},
                run() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || this.target === 0) {
                        this.n = this.target;
                        return;
                    }
                    const duration = 850, start = performance.now();
                    const tick = (now) => {
                        const p = Math.min((now - start) / duration, 1);
                        this.n = this.target * (1 - Math.pow(1 - p, 3));
                        if (p < 1) requestAnimationFrame(tick); else this.n = this.target;
                    };
                    requestAnimationFrame(tick);
                },
            }"
            x-init="run()"
            x-text="n.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec })">{{ number_format((float) $value, (int) $decimals) }}</span>@if ($suffix)<span class="text-base font-bold text-slate-400">{{ $suffix }}</span>@endif
    </p>

    @if ($hint)
        <p class="relative mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</p>
    @endif
</{{ $tag }}>