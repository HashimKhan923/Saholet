@props(['provider'])

<a href="{{ route('shops.show', $provider) }}"
   class="card-lift group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
    <div class="relative z-0 h-24 bg-gradient-to-br from-brand-500 to-brand-700">
        <div class="absolute inset-0 bg-dot-grid opacity-20"></div>
    </div>

    <div class="relative z-10 -mt-10 flex flex-1 flex-col items-center px-5 pb-5 text-center">
        <div class="relative z-10 flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-slate-50 shadow-md dark:border-slate-900 dark:bg-slate-800">
            @if ($provider->shopLogoUrl())
                <img src="{{ $provider->shopLogoUrl() }}" alt="{{ $provider->shopName() }}" class="h-full w-full object-cover">
            @else
                <span class="font-display text-2xl font-extrabold text-brand-600 dark:text-brand-400">{{ mb_substr($provider->shopName(), 0, 1) }}</span>
            @endif
        </div>

        <h3 class="mt-3 w-full truncate font-display text-sm font-bold text-slate-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-400">{{ $provider->shopName() }}</h3>
        @if ($provider->city)
            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $provider->city }}</p>
        @endif

        @php $productsCount = $provider->products_count ?? 0; @endphp
        <p class="mt-2 text-xs font-semibold text-brand-700 dark:text-brand-400">
            {{ $productsCount }} {{ $productsCount === 1 ? 'product' : 'products' }}
        </p>

        <span class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white transition group-hover:bg-brand-700">
            View shop
        </span>
    </div>
</a>
