@extends('layouts.provider')

@section('title', 'Products — ' . config('app.name'))
@section('page_title', 'Products')

@section('content')
<div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Products</h1>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Physical goods customers can buy from your shop, separate from your bookable services.</p>
        </div>
        <a href="{{ route('provider.products.create') }}" class="btn-shine inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            Add product
        </a>
    </div>

    @if (! $profile->canSellProducts())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
            <h2 class="font-display text-base font-bold text-amber-900 dark:text-amber-300">Set up your shop first</h2>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">Configure delivery or pickup before customers can buy from you.</p>
            <a href="{{ route('provider.shop-settings.shipping.edit') }}" class="btn-shine mt-4 inline-flex items-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Go to shop settings</a>
        </div>
    @endif

    @if ($products->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No products yet</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Add your first product to start selling.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <a href="{{ route('provider.products.edit', $product) }}"
                   class="card-lift group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800 {{ $product->is_active ? '' : 'opacity-60' }}">
                    <div class="aspect-video w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if ($product->photos->isNotEmpty())
                            <img src="{{ $product->photos->first()->url() }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center text-slate-300 dark:text-slate-700">
                                <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m3 15 4.5-4.5a2 2 0 0 1 2.8 0L15 15M13.5 13.5 15.5 11.5a2 2 0 0 1 2.8 0L21 14.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-display text-sm font-bold text-slate-900 dark:text-white">{{ $product->name }}</h3>
                            @unless ($product->is_active)
                                <span class="shrink-0 rounded-full {{ $product->deactivation_reason ? 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }} px-2 py-0.5 text-[10px] font-bold uppercase">{{ $product->deactivation_reason ? 'Deactivated' : 'Hidden' }}</span>
                            @endunless
                        </div>
                        @if ($product->category)
                            <p class="mt-0.5 text-xs text-slate-400">{{ $product->category->name }}</p>
                        @endif
                        @if ($product->deactivation_reason)
                            <p class="mt-2 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs text-red-700 dark:bg-red-950/40 dark:text-red-400">{{ $product->deactivation_reason }}</p>
                        @endif
                        <div class="mt-3 flex items-center justify-between">
                            <span class="font-display text-lg font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $product->price, 0) }}</span>
                            <span class="text-xs {{ $product->isInStock() ? 'text-slate-500 dark:text-slate-400' : 'font-semibold text-red-600 dark:text-red-400' }}">
                                {{ $product->isInStock() ? $product->stock_quantity . ' in stock' : 'Out of stock' }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-2">{{ $products->links() }}</div>
    @endif
</div>
@endsection
