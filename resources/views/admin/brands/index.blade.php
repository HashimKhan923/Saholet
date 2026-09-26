@extends('layouts.admin')

@section('title', 'Brands — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Brands</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Logos of brands we've worked with, shown as a scrolling strip on the homepage.</p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New brand</a>
    </div>

    <div class="mt-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total" :value="$counts['total']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        </x-stat-card>
        <x-stat-card label="Active" :value="$counts['active']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m5 12.5 5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Hidden" :value="$counts['hidden']" tone="amber">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.4 5.5A9.9 9.9 0 0 1 12 5c5 0 9 4 10 7-.4 1.2-1.1 2.5-2.1 3.6M6.6 6.6C4.5 8 3 10 2 12c1 3 5 7 10 7 1.3 0 2.5-.3 3.6-.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </x-stat-card>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                <tr>
                    <th class="px-5 py-3">Logo</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Order</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($brands as $brand)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <span class="flex h-12 w-24 items-center justify-center rounded-lg border border-slate-200 bg-white p-1.5 dark:border-slate-700">
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="max-h-full max-w-full object-contain">
                            </span>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $brand->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $brand->sort_order }}</td>
                        <td class="px-5 py-3">
                            @if ($brand->is_active)
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Hidden</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Edit</a>
                                <x-confirm-form :action="route('admin.brands.destroy', $brand)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this brand?" confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No brands yet. Add your first one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
