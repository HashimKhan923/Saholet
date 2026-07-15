@props(['steps' => [], 'progress' => 0])

@php
    $total = count($steps);
    // The first step that isn't done is the one the provider is actively on.
    $currentIndex = collect($steps)->search(fn ($s) => ! $s['done']);
    if ($currentIndex === false) {
        $currentIndex = $total - 1;
    }
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

    {{-- Progress bar --}}
    <div class="flex items-center justify-between">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Verification progress</p>
        <p class="font-display text-sm font-extrabold text-brand-700 dark:text-brand-400">{{ $progress }}%</p>
    </div>

    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-600 transition-[width] duration-700 ease-out"
            style="width: {{ max($progress, 3) }}%"
            role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
    </div>

    {{-- Steps --}}
    <ol class="mt-6 space-y-5 sm:flex sm:space-y-0">
        @foreach ($steps as $i => $step)
            @php
                $done    = (bool) $step['done'];
                $current = ! $done && $i === $currentIndex;
                $isLast  = $i === $total - 1;
            @endphp

            <li class="relative flex flex-1 items-start gap-3 sm:flex-col sm:items-center sm:gap-2 sm:text-center">

                {{-- Connector (desktop only) --}}
                @unless ($isLast)
                    <span aria-hidden="true"
                        class="absolute start-1/2 top-4 hidden h-0.5 w-full sm:block {{ $done ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                @endunless

                {{-- Node --}}
                <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold ring-4 ring-white dark:ring-slate-900
                    @if ($done) bg-brand-600 text-white
                    @elseif ($current) bg-white text-brand-600 ring-4 ring-brand-100 dark:bg-slate-900 dark:ring-brand-950/70
                    @else bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 @endif">

                    @if ($done)
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.8"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </span>

                {{-- Label --}}
                <div class="min-w-0 sm:mt-1">
                    <p class="text-sm font-semibold {{ $done || $current ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                        {{ $step['label'] }}
                    </p>
                    <p class="mt-0.5 text-xs {{ $current ? 'font-medium text-brand-600 dark:text-brand-400' : 'text-slate-400 dark:text-slate-500' }}">
                        {{ $step['hint'] }}
                    </p>
                </div>
            </li>
        @endforeach
    </ol>
</div>