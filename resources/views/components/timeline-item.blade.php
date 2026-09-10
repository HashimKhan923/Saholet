@props(['last' => false, 'dotClass' => 'bg-brand-500'])

{{-- One entry in a vertical event timeline — the connecting line between dots,
     scaled to match booking-timeline's proven top-of-dot-to-top-of-next-dot technique. --}}
<li class="relative flex gap-3 pb-5 last:pb-0">
    @unless ($last)
        <span aria-hidden="true" class="absolute start-[4px] top-4 h-full w-px bg-slate-200 dark:bg-slate-700"></span>
    @endunless
    <span class="relative z-10 mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full ring-4 ring-white dark:ring-slate-900 {{ $dotClass }}"></span>
    <div class="min-w-0 flex-1">
        {{ $slot }}
    </div>
</li>
