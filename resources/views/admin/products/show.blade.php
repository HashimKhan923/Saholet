@extends('layouts.admin')

@section('title', $product->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Shop products</a>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $product->name }}</h1>
        @if ($product->is_active)
            <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
        @else
            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Hidden</span>
        @endif
    </div>

    @if ($product->photos->isNotEmpty())
        <div class="mt-4 flex gap-2 overflow-x-auto">
            @foreach ($product->photos as $photo)
                <img src="{{ $photo->url() }}" class="h-24 w-24 shrink-0 rounded-xl object-cover">
            @endforeach
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Shop</dt>
                <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">
                    <a href="{{ route('admin.providers.show', $product->providerProfile) }}" class="hover:text-brand-700 dark:hover:text-brand-400">
                        {{ $product->providerProfile->business_name ?: $product->providerProfile->user?->name }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Category</dt>
                <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $product->category->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Price</dt>
                <dd class="mt-1 font-display text-lg font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $product->price, 0) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Stock</dt>
                <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $product->stock_quantity }}</dd>
            </div>
            @if ($product->sku)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">SKU</dt>
                    <dd class="mt-1 font-mono font-medium text-slate-800 dark:text-slate-200">{{ $product->sku }}</dd>
                </div>
            @endif
            @if ($product->description)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Description</dt>
                    <dd class="mt-1 text-slate-700 dark:text-slate-300">{{ $product->description }}</dd>
                </div>
            @endif
        </dl>

        @if (! $product->is_active && ($product->deactivation_reason || $product->reactivation_instructions))
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/40">
                @if ($product->deactivation_reason)
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-400">Reason for deactivating</p>
                    <p class="mt-1 text-sm text-red-800 dark:text-red-300">{{ $product->deactivation_reason }}</p>
                @endif
                @if ($product->reactivation_instructions)
                    <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-400">What the provider needs to do</p>
                    <p class="mt-1 text-sm text-red-800 dark:text-red-300">{{ $product->reactivation_instructions }}</p>
                @endif
            </div>
        @endif

        <div class="mt-6 flex items-center gap-2 border-t-2 border-slate-100 pt-5 dark:border-slate-800">
            @if ($product->is_active)
                <x-deactivate-product-modal :product="$product" button-class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" />
            @else
                <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Activate</button>
                </form>
            @endif
            <x-confirm-form :action="route('admin.products.destroy', $product)" method="DELETE"
                button-label="Delete product" button-class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                title="Delete this product?" confirm-label="Delete" />
        </div>
    </div>
</section>
@endsection
