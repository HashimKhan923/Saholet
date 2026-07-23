@props(['service'])

<div class="card-lift group relative flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5 transition-colors duration-300 hover:border-brand-200 hover:bg-brand-50/30 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800 dark:hover:bg-brand-950/10">
    <a href="{{ route('services.show', $service) }}" class="relative block aspect-[4/3] w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
        @if ($service->thumbnail_url)
            <img src="{{ $service->thumbnail_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-110">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-50 to-white dark:from-slate-900 dark:to-slate-800">
                <x-service-icon :name="$service->category->icon ?? 'default'" class="h-10 w-10 text-brand-300 transition-transform duration-500 group-hover:scale-110 dark:text-brand-700" />
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-brand-900/25 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
        <span class="absolute right-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold text-brand-700 shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:bg-brand-600 group-hover:text-white dark:bg-slate-900/90 dark:text-brand-400">
            Rs. {{ number_format($service->base_price, 0) }}
        </span>
    </a>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <a href="{{ route('services.show', $service) }}">
            <h3 class="text-sm font-bold text-slate-900 transition-colors group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $service->name }}</h3>
        </a>
        <p class="mt-0.5 text-xs text-slate-400">~ {{ $service->duration_minutes }} min</p>

        <span class="mt-2 block h-0.5 w-6 rounded-full bg-brand-500 transition-all duration-300 ease-out group-hover:w-12"></span>

        <p class="mt-2 line-clamp-2 min-h-10 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
            {{ $service->description }}
        </p>

        <a href="{{ route('services.show', $service) }}"
           class="btn-shine mt-4 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 py-2.5 text-xs font-semibold text-white shadow-sm transition-all duration-300 hover:bg-brand-700 group-hover:shadow-md group-hover:shadow-brand-600/20">
            {{ __('messages.providers.book_now') }}
            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</div>
