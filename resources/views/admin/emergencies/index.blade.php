@extends('layouts.admin')

@section('title', 'Emergencies — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Emergencies</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review new emergency requests, send price quotes, and assign a provider once the customer accepts.</p>

    @php
        $tabs = [
            'all' => 'All',
            'open' => 'Needs quote',
            'quoted' => 'Quoted',
            'accepted' => 'Ready to assign',
            'matched' => 'Assigned',
            'declined' => 'Declined',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.emergencies.index', ['status' => $key]) }}"
               class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $filter === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                {{ $label }}
                <span class="rounded-full px-1.5 py-0.5 text-[11px] font-bold {{ $filter === $key ? 'bg-white/20' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                    <tr>
                        <th class="px-5 py-3">Reference</th>
                        <th class="px-5 py-3">Service</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">City</th>
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($emergencies as $emergency)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                            <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $emergency->reference }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $emergency->service->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $emergency->consumer->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $emergency->city }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $emergency->quoted_price ? 'Rs. ' . number_format((float) $emergency->quoted_price, 0) : '—' }}</td>
                            <td class="px-5 py-3"><x-emergency-status :status="$emergency->status" /></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.emergencies.show', $emergency) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No emergencies in this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $emergencies->links() }}
</section>
@endsection
