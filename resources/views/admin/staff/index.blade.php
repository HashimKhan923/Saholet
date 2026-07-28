@extends('layouts.admin')

@section('title', 'Staff — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>

    <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Staff</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Internal team accounts with page-level admin access.</p>
        </div>
        <a href="{{ route('admin.staff.create') }}" class="btn-shine rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            + Add staff member
        </a>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.staff.index') }}" class="mt-6 flex items-center gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search name, email, phone…"
            class="w-72 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Search</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Staff member</th>
                    <th class="px-5 py-3">Phone</th>
                    <th class="px-5 py-3">Access</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($staff as $member)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                                    @if ($member->avatar_url)
                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-400">{{ Str::substr($member->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $member->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $member->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $member->phone ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                            @php $moduleCount = count($member->permissions ?? []); @endphp
                            {{ $moduleCount }} {{ Str::plural('page', $moduleCount) }}
                        </td>
                        <td class="px-5 py-3">
                            @if ($member->isSuspended())
                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-950/40 dark:text-red-400">Suspended</span>
                            @else
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.staff.edit', $member) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Edit</a>
                                @if ($member->isSuspended())
                                    <x-confirm-form :action="route('admin.staff.unsuspend', $member)" method="POST"
                                        button-label="Unsuspend" button-class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50 dark:border-brand-900 dark:text-brand-400 dark:hover:bg-brand-950/40"
                                        title="Reinstate this staff account?" confirm-label="Unsuspend" confirm-class="bg-brand-600 hover:bg-brand-700" />
                                @else
                                    <x-confirm-form :action="route('admin.staff.suspend', $member)" method="POST"
                                        button-label="Suspend" button-class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50 dark:border-amber-900 dark:text-amber-400 dark:hover:bg-amber-950/40"
                                        title="Suspend this staff account?" message="They will be immediately signed out and unable to log back in until reinstated." confirm-label="Suspend" confirm-class="bg-amber-600 hover:bg-amber-700" />
                                @endif
                                <x-confirm-form :action="route('admin.staff.destroy', $member)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this staff account?" message="This permanently removes their login and all assigned permissions." confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No staff accounts yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $staff->links() }}</div>
</section>
@endsection
