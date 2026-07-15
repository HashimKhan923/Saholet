@extends('layouts.app')

@section('title', 'Emergency help — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>

    <div class="mt-2 flex items-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M12 8v4M12 15.5v.2" stroke-linecap="round"/></svg>
        </span>
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Request emergency help</h1>
    </div>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">We’ll alert available verified providers in your city right away. The first to accept will be assigned instantly.</p>

    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.emergencies.store') }}" class="mt-8 space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-5">
                <div>
                    <label for="service_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">What do you need?</label>
                    <select id="service_id" name="service_id" required
                        @error('service_id') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('service_id') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <option value="">— Select a service —</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->category->name }} — {{ $service->name }}</option>
                        @endforeach
                    </select>
                    <x-field-error name="service_id" />
                </div>

                <x-address-input label="Your address" :value="old('address')" />

                <x-city-input :value="old('city')" :cities="$cities" />

                <div>
                    <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">What’s happening? <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Briefly describe the emergency…"
                        @error('notes') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('notes') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('notes') }}</textarea>
                    <x-field-error name="notes" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="submitting"
                class="inline-flex flex-1 items-center justify-center rounded-lg bg-red-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Request help now</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Requesting…
                </span>
            </button>
            <a href="{{ route('consumer.dashboard') }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        </div>
        <p class="text-center text-xs text-slate-400 dark:text-slate-500">You’ll only be charged after a provider accepts and completes the job.</p>
    </form>
</section>
@endsection
