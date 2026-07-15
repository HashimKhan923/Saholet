@extends('layouts.admin')

@section('title', 'Corporate accounts — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Corporate accounts</h1>

    <div class="mt-6 grid grid-cols-2 gap-4">
        <x-stat-card label="Total accounts" :value="$counts['total']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="10" height="18" rx="1"/><path d="M14 8h6v13h-6" stroke-linecap="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Total members" :value="$counts['members']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg>
        </x-stat-card>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Company</th>
                    <th class="px-5 py-3">Owner</th>
                    <th class="px-5 py-3">Members</th>
                    <th class="px-5 py-3">Created</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($accounts as $account)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $account->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $account->owner->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $account->members_count }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $account->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.corporate-accounts.show', $account) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No corporate accounts yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $accounts->links() }}
</section>
@endsection
