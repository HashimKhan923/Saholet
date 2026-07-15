@props(['booking'])

@php
    $cancelled = $booking->isCancelled();

    $steps = [
        [
            'label' => 'Requested',
            'hint'  => 'Customer submitted this booking',
            'at'    => $booking->created_at,
        ],
        [
            'label' => 'Confirmed',
            'hint'  => 'You accepted the job',
            'at'    => $booking->confirmed_at,
        ],
        [
            'label' => 'In progress',
            'hint'  => 'Work started on site',
            'at'    => $booking->started_at,
        ],
        [
            'label' => 'Completed',
            'hint'  => 'Job finished — escrow can release',
            'at'    => $booking->completed_at,
        ],
    ];

    if ($cancelled) {
        $steps[] = [
            'label'     => 'Cancelled',
            'hint'      => $booking->cancellation_reason ?: 'This booking was cancelled',
            'at'        => $booking->cancelled_at,
            'cancelled' => true,
        ];
    }
@endphp

<ol class="relative space-y-6">
    @foreach ($steps as $i => $step)
        @php
            $done      = ! is_null($step['at']);
            $isCancel  = $step['cancelled'] ?? false;
            $isLast    = $i === count($steps) - 1;
            // The first not-yet-reached step is "next up" — unless the booking is cancelled.
            $current   = ! $done && ! $cancelled && collect($steps)->take($i)->every(fn ($s) => ! is_null($s['at']));
        @endphp

        <li class="relative flex gap-4">
            {{-- Connector --}}
            @unless ($isLast)
                <span aria-hidden="true"
                    class="absolute start-[15px] top-8 h-full w-0.5 {{ $done && ! $isCancel ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
            @endunless

            {{-- Node --}}
            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-4 ring-white dark:ring-slate-900
                @if ($isCancel && $done) bg-red-500 text-white
                @elseif ($done) bg-brand-600 text-white
                @elseif ($current) bg-white text-brand-600 ring-4 ring-brand-100 dark:bg-slate-900 dark:ring-brand-950
                @else bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600 @endif">

                @if ($isCancel && $done)
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 7l10 10M17 7 7 17" stroke-linecap="round"/></svg>
                @elseif ($done)
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.8"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @elseif ($current)
                    <span class="h-2.5 w-2.5 rounded-full bg-brand-600 animate-live-pulse"></span>
                @else
                    <span class="h-2 w-2 rounded-full bg-current"></span>
                @endif
            </span>

            {{-- Body --}}
            <div class="min-w-0 flex-1 pb-1">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                    <p class="text-sm font-semibold {{ $done || $current ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                        {{ $step['label'] }}
                    </p>
                    @if ($done)
                        <time class="text-xs font-medium text-slate-400" datetime="{{ $step['at']->toIso8601String() }}">
                            {{ $step['at']->format('d M, g:i A') }}
                        </time>
                    @elseif ($current)
                        <span class="text-[11px] font-bold uppercase tracking-wide text-brand-600 dark:text-brand-400">Next</span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs {{ $done || $current ? 'text-slate-500 dark:text-slate-400' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ $step['hint'] }}
                </p>
            </div>
        </li>
    @endforeach
</ol>