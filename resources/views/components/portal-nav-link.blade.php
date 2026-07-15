@props(['href', 'label', 'active' => false, 'badge' => null])

<a href="{{ $href }}"
    class="group relative flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition
        {{ $active
            ? 'bg-gradient-to-r from-brand-50 to-brand-50/40 text-brand-700 shadow-sm shadow-brand-900/[0.03] dark:from-brand-950/60 dark:to-brand-950/10 dark:text-brand-400'
            : 'text-slate-600 hover:translate-x-0.5 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
    @if ($active)
        <span class="absolute inset-y-1.5 start-0 w-1 rounded-full bg-gradient-to-b from-brand-500 to-brand-600"></span>
    @endif
    <span class="flex items-center gap-3">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg transition
            {{ $active
                ? 'bg-white text-brand-600 shadow-sm dark:bg-slate-900 dark:text-brand-400'
                : 'text-slate-400 group-hover:bg-white group-hover:text-slate-600 group-hover:shadow-sm dark:group-hover:bg-slate-900 dark:group-hover:text-slate-300' }}">
            <span class="h-4 w-4">{{ $slot }}</span>
        </span>
        {{ $label }}
    </span>
    @if ($badge)
        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white shadow-sm">{{ $badge }}</span>
    @endif
</a>
