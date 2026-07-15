@extends('layouts.app')

@section('title', 'Post a job — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.jobs.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My jobs</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Post a job</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Describe what you need. Verified providers who offer this service can bid.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.jobs.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-5">
                <div>
                    <label for="service_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Service</label>
                    <select id="service_id" name="service_id" required
                        @error('service_id') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('service_id') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <option value="">— Select a service —</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected((string) old('service_id', $selectedService) === (string) $service->id)>
                                {{ $service->category->name }} — {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-field-error name="service_id" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200">What do you need done?</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Describe the problem or work needed…"
                        @error('description') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('description') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('description') }}</textarea>
                    <x-field-error name="description" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="budget" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Budget (Rs.) <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                        <input id="budget" name="budget" type="number" step="1" min="0" value="{{ old('budget') }}" placeholder="e.g. 2000"
                            @error('budget') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                                @error('budget') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <x-field-error name="budget" />
                    </div>
                    <div>
                        <label for="preferred_date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Preferred date <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                        <input id="preferred_date" name="preferred_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}"
                            @error('preferred_date') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                                @error('preferred_date') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <x-field-error name="preferred_date" />
                    </div>
                </div>

                <x-address-input :value="old('address')" />

                <x-city-input :value="old('city')" :cities="$cities" />

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Photos <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Add photos of the problem so providers can quote more accurately.</p>
                    <div class="mt-1.5">
                        <x-photo-picker />
                    </div>
                    <x-field-error name="photos" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="submitting"
                class="inline-flex items-center rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Post job</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Posting…
                </span>
            </button>
            <a href="{{ route('consumer.jobs.index') }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        </div>
    </form>
</section>
@endsection