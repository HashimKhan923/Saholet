@php
    $checked = (bool) old('is_active', $banner?->is_active ?? true);
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

    <x-admin.image-upload-field
        name="image"
        label="Banner image"
        help="Shown in the homepage banner slider. Wide photos work best — recommended at least 1600×700px (JPG, PNG or WebP, up to 6 MB)."
        :current-url="$banner?->image_url"
        :has-current="(bool) $banner?->image"
        box="h-20 w-36" />

    <div>
        <label for="link_url" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Link <span class="text-slate-400 dark:text-slate-500">(opt)</span></label>
        <input id="link_url" name="link_url" type="text" value="{{ old('link_url', $banner?->link_url) }}"
            @error('link_url') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('link_url') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Where tapping this banner goes — e.g. /services, /maintenance-plans, or a full https:// URL. Leave blank if it shouldn't be tappable.</p>
        <x-field-error name="link_url" />
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $banner?->sort_order ?? 0) }}" required
            @error('sort_order') aria-invalid="true" @enderror
            class="mt-1.5 block w-full max-w-xs rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('sort_order') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <x-field-error name="sort_order" />
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
        <a href="{{ route('admin.banners.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
