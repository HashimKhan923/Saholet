@extends('layouts.admin')

@section('title', ($provider->business_name ?: $provider->user->name) . ' — Shop — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.providers.show', $provider) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; {{ $provider->business_name ?: $provider->user->name }}</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $provider->business_name ?: $provider->user->name }}'s shop</h1>

    <div class="mt-6 grid grid-cols-3 gap-3 sm:max-w-md">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">{{ $products->total() }}</p>
            <p class="text-xs font-bold uppercase tracking-wide text-slate-900 dark:text-slate-200">Products</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">{{ $orders->total() }}</p>
            <p class="text-xs font-bold uppercase tracking-wide text-slate-900 dark:text-slate-200">Orders</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">{{ $ratingCount > 0 ? number_format((float) $ratingAvg, 1) : '—' }}</p>
            <p class="text-xs font-bold uppercase tracking-wide text-slate-900 dark:text-slate-200">Rating ({{ $ratingCount }})</p>
        </div>
    </div>

    {{-- Products --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Products</h2>
        @if ($products->isEmpty())
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No products listed yet.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                        <tr>
                            <th class="py-2 pr-3">Product</th>
                            <th class="py-2 pr-3">Price</th>
                            <th class="py-2 pr-3">Stock</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($products as $product)
                            <tr>
                                <td class="py-2.5 pr-3">
                                    <a href="{{ route('admin.products.show', $product) }}" class="flex items-center gap-2.5 font-medium text-slate-800 hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">
                                        @if ($product->photos->isNotEmpty())
                                            <img src="{{ $product->photos->first()->url() }}" class="h-8 w-8 shrink-0 rounded-md object-cover">
                                        @else
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6h16v12H4z" stroke-linejoin="round"/><path d="m4 16 4.5-4.5 3 3L16 9l4 4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8.5" cy="8.5" r="1.25"/></svg>
                                            </span>
                                        @endif
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $product->price, 0) }}</td>
                                <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400">{{ $product->stock_quantity }}</td>
                                <td class="py-2.5 pr-3">
                                    @if ($product->is_active)
                                        <span class="inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Hidden</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right">
                                    @if ($product->is_active)
                                        <x-deactivate-product-modal :product="$product" />
                                    @else
                                        <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Activate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        @endif
    </div>

    {{-- Orders --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Orders</h2>
        @if ($orders->isEmpty())
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No orders yet.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                        <tr>
                            <th class="py-2 pr-3">Reference</th>
                            <th class="py-2 pr-3">Customer</th>
                            <th class="py-2 pr-3">Total</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="py-2.5 pr-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-slate-800 hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">{{ $order->reference }}</a>
                                </td>
                                <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400">{{ $order->consumer->name ?? '—' }}</td>
                                <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $order->total_amount, 0) }}</td>
                                <td class="py-2.5">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $order->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </div>

    {{-- Rating --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Product reviews</h2>
            @if ($ratingCount > 0)
                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ number_format((float) $ratingAvg, 1) }} ★ · {{ $ratingCount }}</span>
            @endif
        </div>
        @if ($reviews->isEmpty())
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No product reviews yet.</p>
        @else
            <ul class="mt-4 space-y-4">
                @foreach ($reviews as $review)
                    <li class="border-b border-slate-100 pb-4 last:border-0 last:pb-0 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $review->product?->name ?? 'Product' }} · {{ $review->user?->name ?? '—' }}</p>
                            <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $review->comment }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-400">{{ $review->created_at->format('d M Y') }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $reviews->links() }}</div>
        @endif
    </div>
</section>
@endsection
