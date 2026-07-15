@extends('layouts.admin')

@section('title', 'Service areas — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Service areas</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">City-based coverage used when geo-fencing is enabled. Lat/long/radius are stored for reference only in this version.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    {{-- Add --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Add area</h2>
        <form method="POST" action="{{ route('admin.service-areas.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
            x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                <input name="name" type="text" required value="{{ old('name') }}"
                    @error('name') aria-invalid="true" @enderror
                    class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-2 dark:bg-slate-900 dark:text-white
                        @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                <x-field-error name="name" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">City</label>
                <input name="city" type="text" required value="{{ old('city') }}"
                    @error('city') aria-invalid="true" @enderror
                    class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-2 dark:bg-slate-900 dark:text-white
                        @error('city') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                <x-field-error name="city" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Radius km <span class="text-slate-400 dark:text-slate-500">(opt)</span></label>
                <input name="radius_km" type="number" min="1" max="500" value="{{ old('radius_km') }}"
                    @error('radius_km') aria-invalid="true" @enderror
                    class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-2 dark:bg-slate-900 dark:text-white
                        @error('radius_km') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                <x-field-error name="radius_km" />
            </div>
            <div class="flex items-end gap-3">
                <label class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
                    Active
                </label>
                <button type="submit" :disabled="submitting"
                    class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-show="!submitting">Add</span>
                    <span x-show="submitting" x-cloak>Adding…</span>
                </button>
            </div>
        </form>
    </div>

    {{-- List --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">City</th>
                    <th class="px-5 py-3">Radius</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($areas as $area)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.service-areas.update', $area) }}" class="flex flex-wrap items-center gap-2">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $area->name }}" class="w-40 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <input name="city" value="{{ $area->city }}" class="w-32 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <input name="radius_km" type="number" min="1" max="500" value="{{ $area->radius_km }}" class="w-20 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <label class="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400">
                                    <input type="checkbox" name="is_active" value="1" @checked($area->is_active) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
                                    Active
                                </label>
                                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Save</button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $area->city }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $area->radius_km ? $area->radius_km . ' km' : '—' }}</td>
                        <td class="px-5 py-3">
                            @if ($area->is_active)
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <x-confirm-form :action="route('admin.service-areas.destroy', $area)" method="DELETE"
                                button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Remove this area?" confirm-label="Delete" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No service areas yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection