@extends('layouts.admin')

@section('title', 'Shop orders — ' . config('app.name'))

@php
    $tabs = ['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
@endphp

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Shop orders</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $counts['all'] }} total</p>
    </div>

    <div class="mt-6 -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <nav class="flex min-w-max gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @foreach ($tabs as $key => $label)
                @php $active = $filter === $key; @endphp
                <a href="{{ route('admin.orders.index', ['status' => $key]) }}"
                   class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $active ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                    {{ $label }}
                    @if (($counts[$key] ?? 0) > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold {{ $active ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $counts[$key] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                <tr>
                    <th class="px-5 py-3">Reference</th>
                    <th class="px-5 py-3">Customer</th>
                    <th class="px-5 py-3">Shop</th>
                    <th class="px-5 py-3">Total</th>
                    <th class="px-5 py-3">Fulfillment</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($orders as $order)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-400">{{ $order->reference }}</a>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $order->consumer->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $order->providerProfile->business_name ?: $order->providerProfile->user?->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $order->total_amount, 0) }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $order->isPickup() ? 'Pickup' : 'Delivery' }}</td>
                        <td class="px-5 py-3"><x-order-status :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</section>
@endsection
