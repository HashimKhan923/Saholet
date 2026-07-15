@extends('layouts.admin')

@section('title', 'Fraud signals — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Fraud signals</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Heuristic flags for manual review. These are indicators, not proof — investigate before acting.</p>

    {{-- Duplicate phones --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Shared phone numbers</h2>
        @if (empty($duplicatePhones))
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No phone numbers are shared across accounts.</p>
        @else
            <div class="mt-4 space-y-4">
                @foreach ($duplicatePhones as $group)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/40">
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-300">{{ $group['phone'] }} — {{ $group['users']->count() }} accounts</p>
                        <ul class="mt-2 space-y-1 text-sm text-amber-800 dark:text-amber-400">
                            @foreach ($group['users'] as $u)
                                <li>{{ $u->name }} ({{ $u->email }}) · {{ ucfirst($u->role) }}@if ($u->suspended_at) · <span class="font-semibold">suspended</span>@endif</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- High-cancel consumers --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Frequent cancellers</h2>
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Consumers with {{ $cancelThreshold }}+ self-cancelled bookings.</p>
        @if (empty($highCancelConsumers))
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No consumers over the threshold.</p>
        @else
            <ul class="mt-4 space-y-2">
                @foreach ($highCancelConsumers as $row)
                    <li class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800">
                        <span class="text-slate-700 dark:text-slate-300">{{ $row['user']->name }} <span class="text-slate-400 dark:text-slate-500">({{ $row['user']->email }})</span></span>
                        <span class="font-semibold text-red-600 dark:text-red-400">{{ $row['count'] }} cancelled</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Flagged providers --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Providers with disputes / refunds</h2>
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $disputeThreshold }}+ disputes, or any refunds.</p>
        @if (empty($flaggedProviders))
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No providers flagged.</p>
        @else
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-2.5">Provider</th>
                            <th class="px-4 py-2.5">Disputes</th>
                            <th class="px-4 py-2.5">Refunds</th>
                            <th class="px-4 py-2.5 text-right">Refunded total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($flaggedProviders as $row)
                            <tr>
                                <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ $row['profile']->business_name ?: $row['profile']->user->name }} <span class="text-xs text-slate-400 dark:text-slate-500">({{ $row['profile']->user->email }})</span></td>
                                <td class="px-4 py-2.5 {{ $row['disputes'] >= $disputeThreshold ? 'font-semibold text-red-600 dark:text-red-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $row['disputes'] }}</td>
                                <td class="px-4 py-2.5 {{ $row['refunds'] > 0 ? 'font-semibold text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $row['refunds'] }}</td>
                                <td class="px-4 py-2.5 text-right text-slate-700 dark:text-slate-300">Rs. {{ number_format($row['refund_total'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6 text-sm text-slate-500 dark:text-slate-400">
        Take action from the <a href="{{ route('admin.users.index') }}" class="font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300">Users</a> screen.
    </div>
</section>
@endsection