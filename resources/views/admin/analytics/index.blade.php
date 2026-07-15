@extends('layouts.admin')

@section('title', 'Analytics — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Analytics</h1>

    {{-- Money --}}
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-800 dark:bg-brand-950/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-400">Commission earned</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-brand-900 dark:text-brand-300">Rs. {{ number_format($money['commission_earned'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">GMV (completed)</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($money['gmv_completed'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 dark:border-sky-800 dark:bg-sky-950/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-400">Escrow held</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-sky-900 dark:text-sky-300">Rs. {{ number_format($money['escrow_held'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Released</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($money['released'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Refunded</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($money['refunded'], 0) }}</p>
        </div>
    </div>

    {{-- Trend --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Bookings — last 14 days</h2>
        <div class="mt-6" style="height: 220px;">
            <canvas id="trendChart"
                data-labels="{{ collect($trend)->pluck('label')->toJson() }}"
                data-values="{{ collect($trend)->pluck('count')->toJson() }}"></canvas>
        </div>
    </div>

    {{-- Money breakdown chart --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Money breakdown</h2>
        <div class="mt-6" style="height: 220px;">
            <canvas id="moneyChart"
                data-values="{{ json_encode([$money['gmv_completed'], $money['escrow_held'], $money['released'], $money['refunded'], $money['commission_earned']]) }}"></canvas>
        </div>
    </div>

    {{-- Breakdowns --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Users</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Total</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $users['total'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Consumers</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $users['consumers'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Providers</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $users['providers'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Suspended</dt><dd class="font-medium text-red-600">{{ $users['suspended'] }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Providers</h2>
            <div class="mt-4" style="height: 140px;">
                <canvas id="providersChart"
                    data-values="{{ json_encode([$providers['approved'], $providers['pending'], $providers['rejected'], $providers['draft']]) }}"></canvas>
            </div>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Approved</dt><dd class="font-semibold text-brand-700">{{ $providers['approved'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Pending</dt><dd class="font-medium text-amber-600">{{ $providers['pending'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Rejected</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $providers['rejected'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Draft</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $providers['draft'] }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Bookings</h2>
            <div class="mt-4" style="height: 140px;">
                <canvas id="bookingsChart"
                    data-values="{{ json_encode([$bookings['pending'], $bookings['confirmed'], $bookings['in_progress'], $bookings['completed'], $bookings['cancelled']]) }}"></canvas>
            </div>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Total</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $bookings['total'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Completed</dt><dd class="font-medium text-brand-700">{{ $bookings['completed'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Active</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $bookings['pending'] + $bookings['confirmed'] + $bookings['in_progress'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Cancelled</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $bookings['cancelled'] }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Reviews</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Total</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $reviews['count'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Avg rating</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $reviews['count'] ? number_format($reviews['avg'], 2) : '—' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Disputes</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Open</dt><dd class="font-semibold text-red-600">{{ $disputes['open'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Resolved</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $disputes['resolved'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Dismissed</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $disputes['dismissed'] }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Marketplace</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Open jobs</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $openJobs }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Contracts</h2>
            @if ($contracts['total'] > 0)
                <div class="mt-4" style="height: 140px;">
                    <canvas id="contractsChart"
                        data-values="{{ json_encode([$contracts['submitted'], $contracts['quoted'], $contracts['accepted'], $contracts['in_progress'], $contracts['completed'], $contracts['rejected'] + $contracts['cancelled']]) }}"></canvas>
                </div>
            @endif
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Total requests</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $contracts['total'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Awaiting quote</dt><dd class="font-medium text-amber-600">{{ $contracts['submitted'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">In progress</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $contracts['in_progress'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Acceptance rate</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $contracts['acceptance_rate'] !== null ? $contracts['acceptance_rate'] . '%' : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Quoted value</dt><dd class="font-semibold text-brand-700">Rs. {{ number_format($contracts['quoted_value'], 0) }}</dd></div>
            </dl>
            <a href="{{ route('admin.contracts.index') }}" class="mt-4 inline-flex text-xs font-semibold text-brand-700 hover:text-brand-800">Review contracts →</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Careers</h2>
            @if ($careers['applications_total'] > 0)
                <div class="mt-4" style="height: 140px;">
                    <canvas id="careersChart"
                        data-values="{{ json_encode([$careers['submitted'], $careers['shortlisted'], $careers['hired'], $careers['rejected'], $careers['withdrawn']]) }}"></canvas>
                </div>
            @endif
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Open listings</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $careers['open_listings'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Total applications</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $careers['applications_total'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">In review / interview</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $careers['shortlisted'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">Hire rate</dt><dd class="font-medium text-slate-800 dark:text-slate-300">{{ $careers['hire_rate'] !== null ? $careers['hire_rate'] . '%' : '—' }}</dd></div>
            </dl>
            <a href="{{ route('admin.careers.index') }}" class="mt-4 inline-flex text-xs font-semibold text-brand-700 hover:text-brand-800">Manage listings →</a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (! window.Chart) return;

    const isDark = document.documentElement.classList.contains('dark');
    const brand = { 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e' };
    const grid = { color: isDark ? '#1e293b' : '#f1f5f9' };
    const ticks = { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } };

    Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';

    const trendEl = document.getElementById('trendChart');
    if (trendEl) {
        new Chart(trendEl, {
            type: 'line',
            data: {
                labels: JSON.parse(trendEl.dataset.labels),
                datasets: [{
                    label: 'Bookings',
                    data: JSON.parse(trendEl.dataset.values),
                    borderColor: brand[600],
                    backgroundColor: 'rgba(13, 148, 136, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: brand[600],
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks },
                    y: { beginAtZero: true, grid, ticks, },
                },
            },
        });
    }

    const moneyEl = document.getElementById('moneyChart');
    if (moneyEl) {
        new Chart(moneyEl, {
            type: 'bar',
            data: {
                labels: ['GMV', 'Escrow held', 'Released', 'Refunded', 'Commission'],
                datasets: [{
                    data: JSON.parse(moneyEl.dataset.values),
                    backgroundColor: ['#64748b', '#0ea5e9', brand[600], '#ef4444', brand[500]],
                    borderRadius: 6,
                    maxBarThickness: 48,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks },
                    y: { beginAtZero: true, grid, ticks },
                },
            },
        });
    }

    const donutOptions = {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } },
    };

    const providersEl = document.getElementById('providersChart');
    if (providersEl) {
        new Chart(providersEl, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected', 'Draft'],
                datasets: [{
                    data: JSON.parse(providersEl.dataset.values),
                    backgroundColor: [brand[600], '#f59e0b', '#ef4444', '#94a3b8'],
                    borderWidth: 0,
                }],
            },
            options: donutOptions,
        });
    }

    const bookingsEl = document.getElementById('bookingsChart');
    if (bookingsEl) {
        new Chart(bookingsEl, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'In progress', 'Completed', 'Cancelled'],
                datasets: [{
                    data: JSON.parse(bookingsEl.dataset.values),
                    backgroundColor: ['#f59e0b', '#0ea5e9', '#6366f1', brand[600], '#ef4444'],
                    borderWidth: 0,
                }],
            },
            options: donutOptions,
        });
    }

    const contractsEl = document.getElementById('contractsChart');
    if (contractsEl) {
        new Chart(contractsEl, {
            type: 'doughnut',
            data: {
                labels: ['Awaiting quote', 'Quoted', 'Accepted', 'In progress', 'Completed', 'Rejected/Cancelled'],
                datasets: [{
                    data: JSON.parse(contractsEl.dataset.values),
                    backgroundColor: ['#f59e0b', '#0ea5e9', brand[500], brand[600], '#6366f1', '#94a3b8'],
                    borderWidth: 0,
                }],
            },
            options: donutOptions,
        });
    }

    const careersEl = document.getElementById('careersChart');
    if (careersEl) {
        new Chart(careersEl, {
            type: 'doughnut',
            data: {
                labels: ['Submitted', 'In review/interview', 'Hired', 'Rejected', 'Withdrawn'],
                datasets: [{
                    data: JSON.parse(careersEl.dataset.values),
                    backgroundColor: ['#f59e0b', '#0ea5e9', brand[600], '#ef4444', '#94a3b8'],
                    borderWidth: 0,
                }],
            },
            options: donutOptions,
        });
    }
});
</script>
@endsection