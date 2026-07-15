@extends('layouts.admin')

@section('title', 'Settings — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Platform settings</h1>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Commission</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The platform’s cut, taken from escrow when a payment is released. Per-category overrides can be set in each category.</p>
            <div class="mt-4 max-w-xs">
                <label for="commission_rate" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Global commission (%)</label>
                <input id="commission_rate" name="commission_rate" type="number" step="0.5" min="0" max="50"
                    value="{{ old('commission_rate', $commissionRate) }}" required
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Geo-fencing</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">When enabled, bookings and job posts are restricted to your active service areas (city-based).</p>
            <label class="mt-4 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="geofencing_enabled" value="1" @checked(old('geofencing_enabled', $geofencingEnabled))
                    class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
                Enable geo-fencing
            </label>
            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Manage areas under <a href="{{ route('admin.service-areas.index') }}" class="font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300">Service areas</a>.</p>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Save settings</button>
    </form>
</section>
@endsection