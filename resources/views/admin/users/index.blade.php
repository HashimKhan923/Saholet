@extends('layouts.admin')

@section('title', 'Users — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Users</h1>

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <x-stat-card label="Total" :value="$counts['total']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5M16 11h5M18.5 8.5v5" stroke-linecap="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Consumers" :value="$counts['consumers']" tone="sky">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Providers" :value="$counts['providers']" tone="violet">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke-linecap="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Job seekers" :value="$counts['job_seekers']" tone="amber">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </x-stat-card>
        <x-stat-card label="Suspended" :value="$counts['suspended']" tone="red">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M8.5 8.5l7 7" stroke-linecap="round"/></svg>
        </x-stat-card>
    </div>

    @php $tabs = ['all' => 'All', 'consumer' => 'Consumers', 'provider' => 'Providers', 'job_seeker' => 'Job seekers']; @endphp
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.users.index', ['role' => $key, 'q' => $q ?: null]) }}"
                   class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $role === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="role" value="{{ $role }}">
            <input type="search" name="q" value="{{ $q }}" placeholder="Search name, email, phone…"
                class="w-56 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Search</button>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Role</th>
                    <th class="px-5 py-3">Phone</th>
                    <th class="px-5 py-3">Joined</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ ucfirst($user->role) }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $user->phone ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            @if ($user->isSuspended())
                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">Suspended</span>
                            @else
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if ($user->isSuspended())
                                <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Reinstate</button>
                                </form>
                            @else
                                <x-confirm-form :action="route('admin.users.suspend', $user)"
                                    button-label="Suspend" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Suspend this account?" message="They will be unable to log in until reinstated."
                                    confirm-label="Suspend" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No users in this view.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</section>
@endsection