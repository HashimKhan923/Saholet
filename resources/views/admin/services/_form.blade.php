@php
    $checked = (bool) old('is_active', $service?->is_active ?? true);
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        Please fix the highlighted fields below.
    </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="category_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Category</label>
        <select id="category_id" name="category_id" required
            @error('category_id') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('category_id') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <option value="">— Select a category —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $service?->category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-field-error name="category_id" />
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $service?->name) }}" required
            @error('name') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <x-field-error name="name" />
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
        <textarea id="description" name="description" rows="3"
            @error('description') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('description') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('description', $service?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <x-admin.image-upload-field
        name="thumbnail"
        label="Thumbnail"
        help="Shown next to this service in listings and on its detail page. Recommended at least 1200×675px, landscape (JPG, PNG or WebP, up to 4 MB)."
        :current-url="$service?->thumbnail_url"
        :has-current="(bool) $service?->thumbnail" />

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="base_price" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Base price (Rs.)</label>
            <input id="base_price" name="base_price" type="number" step="1" min="0" value="{{ old('base_price', $service ? (int) $service->base_price : '') }}" required
                @error('base_price') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('base_price') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <x-field-error name="base_price" />
        </div>

        <div>
            <label for="duration_minutes" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Duration (minutes)</label>
            <input id="duration_minutes" name="duration_minutes" type="number" step="5" min="5" max="1440" value="{{ old('duration_minutes', $service?->duration_minutes ?? 60) }}" required
                @error('duration_minutes') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('duration_minutes') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <x-field-error name="duration_minutes" />
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked($checked)
            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
        Active (visible to consumers)
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="submitting"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $submitLabel }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ route('admin.services.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
