@extends('layouts.admin')

@section('title', 'Service areas — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Service areas</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Used when geo-fencing is enabled. A location is served only if it falls inside a drawn boundary — anywhere else is treated as outside.</p>
        </div>
        <a href="{{ route('admin.service-areas.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New area</a>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border p-5 {{ $geofencingEnabled ? 'border-brand-200 bg-brand-50 dark:border-brand-900 dark:bg-brand-950/30' : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/60' }}">
        <div>
            <p class="flex items-center gap-2 text-sm font-bold {{ $geofencingEnabled ? 'text-brand-800 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="inline-block h-2.5 w-2.5 rounded-full {{ $geofencingEnabled ? 'bg-brand-600' : 'bg-slate-400' }}"></span>
                Geo-fencing is currently {{ $geofencingEnabled ? 'ON' : 'OFF' }}
            </p>
            <p class="mt-1 text-xs {{ $geofencingEnabled ? 'text-brand-700 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400' }}">
                @if ($geofencingEnabled)
                    Bookings, emergencies, job posts, contracts, subscriptions, and saved addresses are all restricted to the areas below.
                @else
                    Nothing is being restricted right now — the areas below are configured but not enforced.
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('admin.service-areas.geofencing') }}">
            @csrf
            <input type="hidden" name="geofencing_enabled" value="{{ $geofencingEnabled ? '0' : '1' }}">
            <button type="submit" class="rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm transition {{ $geofencingEnabled ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' : 'bg-brand-600 text-white hover:bg-brand-700' }}">
                {{ $geofencingEnabled ? 'Turn off' : 'Turn on' }}
            </button>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Coverage</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($areas as $area)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                            <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $area->name }}</td>
                            <td class="px-5 py-3">
                                @if ($area->hasBoundary())
                                    <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/40 dark:text-sky-400">{{ count($area->boundary) }} points</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-950/40 dark:text-red-400">No boundary — has no effect</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($area->is_active)
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.service-areas.edit', $area) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Edit</a>
                                    <x-confirm-form :action="route('admin.service-areas.destroy', $area)" method="DELETE"
                                        button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                        title="Remove this area?" confirm-label="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No service areas yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
