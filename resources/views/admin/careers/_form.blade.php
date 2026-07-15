@php
    $closesAtValue = old('closes_at', $listing?->closes_at?->format('Y-m-d\TH:i'));
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

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="career_category_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Category</label>
            <select id="career_category_id" name="career_category_id" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">— Select —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('career_category_id', $listing?->career_category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <x-field-error name="career_category_id" />
        </div>
        <div>
            <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title', $listing?->title) }}" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="title" />
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
        <textarea id="description" name="description" rows="5" required
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $listing?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div>
        <label for="requirements" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Requirements <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
        <textarea id="requirements" name="requirements" rows="4"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('requirements', $listing?->requirements) }}</textarea>
        <x-field-error name="requirements" />
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="employment_type" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Employment type</label>
            <select id="employment_type" name="employment_type" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                @foreach (\App\Models\CareerListing::EMPLOYMENT_TYPES as $type)
                    <option value="{{ $type }}" @selected(old('employment_type', $listing?->employment_type ?? 'full_time') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="city" class="block text-sm font-medium text-slate-700 dark:text-slate-300">City <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <input id="city" name="city" type="text" value="{{ old('city', $listing?->city) }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
            <select id="status" name="status" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                @foreach (['draft', 'open', 'closed', 'filled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $listing?->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="salary_min" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Salary min (Rs.) <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <input id="salary_min" name="salary_min" type="number" step="1" min="0" value="{{ old('salary_min', $listing?->salary_min !== null ? (float) $listing->salary_min : '') }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>
        <div>
            <label for="salary_max" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Salary max (Rs.) <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <input id="salary_max" name="salary_max" type="number" step="1" min="0" value="{{ old('salary_max', $listing?->salary_max !== null ? (float) $listing->salary_max : '') }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="salary_max" />
        </div>
        <div>
            <label for="closes_at" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Closes at <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <input id="closes_at" name="closes_at" type="datetime-local" value="{{ $closesAtValue }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_remote" value="1" @checked((bool) old('is_remote', $listing?->is_remote))
            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900">
        Remote friendly
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="submitting"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $submitLabel }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ route('admin.careers.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
