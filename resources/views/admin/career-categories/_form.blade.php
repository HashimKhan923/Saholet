@php
    $checked = (bool) old('is_active', $category?->is_active ?? true);
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
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required
            @error('name') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-700 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <x-field-error name="name" />
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
        <textarea id="description" name="description" rows="3"
            @error('description') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('description') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-700 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('description', $category?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" required
            @error('sort_order') aria-invalid="true" @enderror
            class="mt-1.5 block w-full max-w-xs rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('sort_order') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-700 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <x-field-error name="sort_order" />
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked($checked)
            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900">
        Active (visible on the careers page)
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="submitting"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $submitLabel }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ route('admin.career-categories.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
