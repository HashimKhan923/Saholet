@extends('layouts.admin')

@section('title', 'Disputes — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Disputes</h1>

    @php
        $tabs = [
            'open' => 'Open (' . $counts['open'] . ')',
            'resolved' => 'Resolved (' . $counts['resolved'] . ')',
            'dismissed' => 'Dismissed (' . $counts['dismissed'] . ')',
            'all' => 'All',
        ];
    @endphp
    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.disputes.index', ['status' => $key]) }}"
               class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $status === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Reference</th>
                    <th class="px-5 py-3">Booking</th>
                    <th class="px-5 py-3">Opened by</th>
                    <th class="px-5 py-3">Submitted</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($disputes as $dispute)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $dispute->reference }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                            {{ $dispute->booking->service->name }}
                            <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $dispute->booking->reference }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $dispute->opener->name }} <span class="text-xs text-slate-400 dark:text-slate-500">({{ $dispute->opened_by_role }})</span></td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $dispute->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3"><x-dispute-status :status="$dispute->status" /></td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No disputes in this view.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $disputes->links() }}
</section>
@endsection