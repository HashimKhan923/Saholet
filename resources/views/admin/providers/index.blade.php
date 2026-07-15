@extends('layouts.admin')

@section('title', 'Provider approvals — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Provider approvals</h1>

    {{-- Filter tabs --}}
    @php
        $tabs = [
            'pending' => 'Pending (' . $counts['pending'] . ')',
            'approved' => 'Approved (' . $counts['approved'] . ')',
            'rejected' => 'Rejected (' . $counts['rejected'] . ')',
            'all' => 'All',
        ];
    @endphp
    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.providers.index', ['status' => $key]) }}"
               class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $status === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Provider</th>
                    <th class="px-5 py-3">Business</th>
                    <th class="px-5 py-3">City</th>
                    <th class="px-5 py-3">Submitted</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($profiles as $profile)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $profile->user->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $profile->user->email }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $profile->business_name ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $profile->city ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $profile->submitted_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @switch($profile->status)
                                @case('approved')
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Approved</span>
                                    @break
                                @case('pending')
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Pending</span>
                                    @break
                                @case('rejected')
                                    <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">Rejected</span>
                                    @break
                                @default
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Draft</span>
                            @endswitch
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.providers.show', $profile) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No applications in this view.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $profiles->links() }}
</section>
@endsection