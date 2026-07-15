@extends('layouts.app')

@section('title', 'My addresses — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My addresses</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Save your common addresses to fill booking forms in one click.</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Existing addresses --}}
    <div class="mt-6 space-y-3">
        @forelse ($addresses as $address)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ editing: false }">
                <div x-show="!editing">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $address->label }}</p>
                                @if ($address->is_default)
                                    <span class="inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Default</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $address->address }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $address->city }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" @click="editing = true" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Edit</button>
                            <x-confirm-form :action="route('consumer.addresses.destroy', $address)" method="DELETE"
                                button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Delete this address?" confirm-label="Delete" />
                        </div>
                    </div>
                </div>

                <form x-show="editing" x-cloak method="POST" action="{{ route('consumer.addresses.update', $address) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input type="text" name="label" value="{{ $address->label }}" maxlength="60" required placeholder="Label (e.g. Home, Office)"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <input type="text" name="city" value="{{ $address->city }}" maxlength="120" required placeholder="City"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    </div>
                    <textarea name="address" rows="2" required placeholder="Full address" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ $address->address }}</textarea>
                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                        <input type="checkbox" name="is_default" value="1" @checked($address->is_default) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                        Make default
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">Save</button>
                        <button type="button" @click="editing = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No saved addresses yet</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Add one below to reuse it across bookings, jobs, and contracts.</p>
            </div>
        @endforelse
    </div>

    {{-- Add new --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Add an address</h2>
        <form method="POST" action="{{ route('consumer.addresses.store') }}" class="mt-4 space-y-3">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Label</label>
                    <input type="text" name="label" value="{{ old('label') }}" maxlength="60" required placeholder="e.g. Home, Office"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" maxlength="120" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Full address</label>
                <textarea name="address" rows="2" required placeholder="House, street, area" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('address') }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                Make default
            </label>
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Save address</button>
        </form>
    </div>
</section>
@endsection
