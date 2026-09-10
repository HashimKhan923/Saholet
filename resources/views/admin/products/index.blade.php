@extends('layouts.admin')

@section('title', 'Shop products — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Shop products</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Products publish immediately when a provider adds them — moderate here after the fact.</p>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mt-6 flex items-center gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search products, SKU, shop…"
            class="w-72 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Search</button>
        @if ($q)
            <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">Clear</a>
        @endif
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                <tr>
                    <th class="px-5 py-3">Product</th>
                    <th class="px-5 py-3">Shop</th>
                    <th class="px-5 py-3">Price</th>
                    <th class="px-5 py-3">Stock</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($products as $product)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.products.show', $product) }}" class="font-medium text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-400">{{ $product->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $product->providerProfile->business_name ?: $product->providerProfile->user?->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $product->price, 0) }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $product->stock_quantity }}</td>
                        <td class="px-5 py-3">
                            @if ($product->is_active)
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Hidden</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($product->is_active)
                                    <x-deactivate-product-modal :product="$product" />
                                @else
                                    <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Activate</button>
                                    </form>
                                @endif
                                <x-confirm-form :action="route('admin.products.destroy', $product)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this product?" confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</section>
@endsection
