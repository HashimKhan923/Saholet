@extends('layouts.app')

@section('title', 'New contract — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.contracts.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My contracts</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Request a contract</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Building something bigger — a new home, a renovation? List every service you need and our team will send you a single quote with a payment schedule.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.contracts.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6"
          x-data="{
              submitting: false,
              items: @js(old('items', [['service_id' => '', 'quantity' => 1, 'notes' => '']])),
              addItem() { this.items.push({ service_id: '', quantity: 1, notes: '' }) },
              removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1) },
          }"
          @submit="submitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Project title</label>
                    <input id="title" name="title" type="text" required value="{{ old('title') }}" placeholder="e.g. New house construction — Phase 1"
                        @error('title') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('title') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                    <x-field-error name="title" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Project description</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Describe the overall project, scope, and any details that will help us quote accurately…"
                        @error('description') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('description') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('description') }}</textarea>
                    <x-field-error name="description" />
                </div>

                <div>
                    <label for="preferred_start_date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Preferred start date <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                    <input id="preferred_start_date" name="preferred_start_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('preferred_start_date') }}"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <x-field-error name="preferred_start_date" />
                </div>

                <x-address-input :value="old('address')" />

                <x-city-input :value="old('city')" :cities="$cities" />
            </div>
        </div>

        {{-- Services --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Services needed</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Add every service this project requires — we'll price each one in your quote.</p>

            <div class="mt-4 space-y-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="'Item ' + (index + 1)"></span>
                            <button type="button" x-show="items.length > 1" @click="removeItem(index)" class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Remove</button>
                        </div>

                        <div class="mt-3 grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Service</label>
                                <select :name="'items[' + index + '][service_id]'" x-model="item.service_id" required
                                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    <option value="">— Select —</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->category->name }} — {{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Quantity</label>
                                <input type="number" min="1" max="100" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Notes <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                            <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes" placeholder="Any specifics for this service…"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="addItem()" class="mt-4 inline-flex items-center rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-600 dark:text-slate-300 dark:hover:border-brand-700 dark:hover:text-brand-400">+ Add another service</button>
            <x-field-error name="items" />
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Reference photos <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Site photos, plans, or inspiration help us quote more accurately.</p>
            <div class="mt-1.5">
                <x-photo-picker name="photos" :max="8" />
            </div>
            <x-field-error name="photos" />
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="submitting"
                class="inline-flex items-center rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Submit contract request</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Submitting…
                </span>
            </button>
            <a href="{{ route('consumer.contracts.index') }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        </div>
    </form>
</section>
@endsection
