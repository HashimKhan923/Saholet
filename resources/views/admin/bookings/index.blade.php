@extends('layouts.admin')

@section('title', 'Bookings — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Bookings</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Every direct booking, bid-won job, contract visit, subscription visit, and accepted emergency across the platform.</p>

    @php
        $tabs = [
            'all' => 'All',
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.bookings.index', ['status' => $key, 'q' => $search ?: null]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $filter === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                    <span class="rounded-full px-1.5 py-0.5 text-[11px] font-bold {{ $filter === $key ? 'bg-white/20' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" class="w-full sm:w-64">
            <input type="hidden" name="status" value="{{ $filter }}">
            <div class="relative">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="Reference, consumer, provider, service…"
                    class="block w-full rounded-lg border border-slate-300 bg-white py-2 ps-9 pe-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Booking</th>
                        <th class="px-5 py-3">Consumer</th>
                        <th class="px-5 py-3">Provider</th>
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Scheduled</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $booking->service->name ?? '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->reference }}</div>
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $booking->consumer->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $booking->providerProfile->business_name ?? $booking->providerProfile->user->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $booking->price, 0) }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                                {{ \Illuminate\Support\Carbon::parse($booking->scheduled_date)->format('d M Y') }}
                                <span class="text-xs text-slate-400">{{ substr($booking->scheduled_time, 0, 5) }}</span>
                            </td>
                            <td class="px-5 py-3"><x-booking-status :status="$booking->status" /></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No bookings in this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $bookings->links() }}
</section>
@endsection
