@extends('layouts.admin')

@section('title', 'Provider settlements — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Provider settlements</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Commission providers owe from cash bookings, being paid back.</p>

    @php
        $tabs = [
            'all' => 'All',
            'pending' => 'Pending (' . $counts['pending'] . ')',
            'confirmed' => 'Confirmed',
            'rejected' => 'Rejected',
        ];
        $statusTones = [
            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
            'confirmed' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
            'rejected' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
        ];
    @endphp
    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.settlements.index', ['status' => $key]) }}"
               class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $status === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                <tr>
                    <th class="px-5 py-3">Provider</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Method</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Submitted</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($settlements as $s)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $s->providerProfile->business_name ?: $s->providerProfile->user->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $s->reference }}</div>
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">
                            Rs. {{ number_format((float) $s->amount, 0) }}
                            @if ($s->confirmed_amount !== null && (float) $s->confirmed_amount !== (float) $s->amount)
                                <span class="block text-xs font-normal text-slate-400">confirmed Rs. {{ number_format((float) $s->confirmed_amount, 0) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $s->methodLabel() }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTones[$s->status] ?? '' }}">{{ ucfirst($s->status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $s->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.settlements.show', $s) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No settlements in this view.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $settlements->links() }}
</section>
@endsection
