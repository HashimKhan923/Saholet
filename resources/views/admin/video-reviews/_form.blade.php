@php
    $checked = (bool) old('is_active', $videoReview?->is_active ?? true);
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        Please fix the highlighted fields below.
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-5"
      x-data="{
        submitting: false,
        url: @js(old('youtube_url', $videoReview?->youtube_url ?? '')),
        get thumb() {
            const m = this.url.match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
            return m ? `https://img.youtube.com/vi/${m[1]}/hqdefault.jpg` : null;
        },
      }" @submit="submitting = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
        <input id="title" name="title" type="text" value="{{ old('title', $videoReview?->title) }}" required
            @error('title') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('title') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <x-field-error name="title" />
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Short description <span class="text-slate-400 dark:text-slate-500">(opt)</span></label>
        <input id="description" name="description" type="text" maxlength="500" value="{{ old('description', $videoReview?->description) }}"
            @error('description') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('description') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Shown under the title on the homepage carousel card.</p>
        <x-field-error name="description" />
    </div>

    <div>
        <label for="youtube_url" class="block text-sm font-medium text-slate-700 dark:text-slate-300">YouTube link</label>
        <input id="youtube_url" name="youtube_url" type="text" x-model="url" required
            placeholder="https://www.youtube.com/watch?v=..."
            @error('youtube_url') aria-invalid="true" @enderror
            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('youtube_url') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Paste the full YouTube video URL — watch, youtu.be, or shorts links all work.</p>
        <x-field-error name="youtube_url" />

        <img x-show="thumb" :src="thumb" x-cloak alt="" class="mt-3 h-24 w-40 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $videoReview?->sort_order ?? 0) }}" required
            @error('sort_order') aria-invalid="true" @enderror
            class="mt-1.5 block w-full max-w-xs rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                @error('sort_order') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
        <x-field-error name="sort_order" />
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked($checked)
            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
        Active (visible on the homepage)
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="submitting"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $submitLabel }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ route('admin.video-reviews.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
