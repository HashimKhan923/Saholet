@php
    $checked = (bool) old('is_active', $plan?->is_active ?? true);
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        Please fix the highlighted fields below.
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Plan name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $plan?->name) }}" required placeholder="e.g. AC Servicing — Biannual"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <x-field-error name="name" />
    </div>

    <div>
        <label for="service_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Service performed each visit</label>
        <select id="service_id" name="service_id" required
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">Select a service…</option>
            @foreach ($services as $service)
                <option value="{{ $service->id }}" @selected((int) old('service_id', $plan?->service_id) === $service->id)>{{ $service->name }}</option>
            @endforeach
        </select>
        <x-field-error name="service_id" />
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
        <textarea id="description" name="description" rows="3"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $plan?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="frequency_months" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Visit every (months)</label>
            <input id="frequency_months" name="frequency_months" type="number" min="1" max="24" value="{{ old('frequency_months', $plan?->frequency_months ?? 6) }}" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="frequency_months" />
        </div>

        <div>
            <label for="total_visits" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Total visits <span class="text-slate-400 dark:text-slate-500">(blank = ongoing)</span></label>
            <input id="total_visits" name="total_visits" type="number" min="1" max="100" value="{{ old('total_visits', $plan?->total_visits) }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="total_visits" />
        </div>

        <div>
            <label for="price_per_visit" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Price per visit (PKR)</label>
            <input id="price_per_visit" name="price_per_visit" type="number" step="1" min="0" value="{{ old('price_per_visit', $plan?->price_per_visit) }}" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="price_per_visit" />
        </div>
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" required
            class="mt-1.5 block w-40 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <x-field-error name="sort_order" />
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked($checked)
            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
        Active (visible to consumers on /plans)
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="submitting"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $submitLabel }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ route('admin.subscription-plans.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
