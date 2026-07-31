@props(['service'])

<div class="card-lift group flex flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-colors duration-300 hover:border-brand-200 hover:bg-brand-50/30 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800 dark:hover:bg-brand-950/10">
    <div class="flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-transform duration-300 group-hover:scale-105 dark:bg-brand-950/50 dark:text-brand-400">
            <x-service-icon :name="$service->category->icon ?? 'default'" class="h-6 w-6" />
        </span>
        <div>
            <a href="{{ route('services.show', $service) }}">
                <h3 class="font-display text-base font-bold text-slate-900 transition-colors group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $service->name }}</h3>
            </a>
            <span class="mt-1 inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">~ {{ $service->duration_minutes }} min</span>
        </div>
    </div>

    <p class="mt-4 line-clamp-3 min-h-15 text-sm text-slate-500 dark:text-slate-400">
        {{ $service->description }}
    </p>

    <div class="mt-auto border-t border-slate-100 pt-5 dark:border-slate-800">
        <p class="font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($service->base_price, 0) }}</p>
        <p class="text-xs text-slate-400">starting price</p>
    </div>

    <a href="{{ route('services.show', $service) }}"
       class="btn-shine mt-5 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-300 hover:bg-brand-700 group-hover:shadow-md group-hover:shadow-brand-600/20">
        {{ __('messages.providers.book_now') }}
        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
</div>
