@php
    $iconKeys = array_keys(config('services_catalog.icons'));
    $checked = (bool) old('is_active', $category?->is_active ?? true);
    $globalRate = \App\Models\Setting::get('commission_rate', \App\Services\CommissionService::DEFAULT_RATE);
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        Please fix the highlighted fields below.
    </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5" x-data="{ submitting: false, preview: null }" @submit="submitting = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required
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
                @error('description') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('description', $category?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Card image <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Shown behind the category card on the homepage. Landscape photos work best (JPG, PNG or WebP, up to 4&nbsp;MB).</p>

        <div class="mt-2.5 flex items-center gap-4">
            <div class="h-20 w-28 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                <template x-if="preview">
                    <img :src="preview" alt="" class="h-full w-full object-cover">
                </template>
                @if ($category?->image_url)
                    <img x-show="!preview" src="{{ $category->image_url }}" alt="" class="h-full w-full object-cover">
                @endif
                <div x-show="!preview" @if ($category?->image_url) style="display: none" @endif class="flex h-full w-full items-center justify-center text-slate-300 dark:text-slate-600">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m3 16 5-5 4 4 3-3 6 6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8" cy="9" r="1.5"/></svg>
                </div>
            </div>

            <div class="flex-1">
                <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp"
                    @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                    @error('image') aria-invalid="true" @enderror
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3.5 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-300 dark:file:bg-brand-950/50 dark:file:text-brand-400">
                <x-field-error name="image" />

                @if ($category?->image)
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" name="remove_image" value="1"
                            class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-200 dark:border-slate-600 dark:bg-slate-800">
                        Remove current image
                    </label>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="icon" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Icon</label>
            <select id="icon" name="icon"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                @foreach ($iconKeys as $key)
                    <option value="{{ $key }}" @selected(old('icon', $category?->icon ?? 'default') === $key)>{{ ucfirst($key) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="commission_rate" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Commission % <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <input id="commission_rate" name="commission_rate" type="number" step="0.5" min="0" max="50"
                value="{{ old('commission_rate', $category?->commission_rate !== null ? (float) $category->commission_rate : '') }}"
                placeholder="Global: {{ $globalRate }}%"
                @error('commission_rate') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('commission_rate') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Leave blank to use the global rate.</p>
            <x-field-error name="commission_rate" />
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sort order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" required
                @error('sort_order') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('sort_order') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <x-field-error name="sort_order" />
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
        <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
