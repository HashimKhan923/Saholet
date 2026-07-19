@props([
    'name',
    'label',
    'help' => null,
    'currentUrl' => null,
    'hasCurrent' => false,
    'removeName' => null,
    'box' => 'h-20 w-28',
])

@php
    $removeName ??= 'remove_' . $name;
@endphp

<div x-data="{ preview: null }">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }} <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
    @if ($help)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
    @endif

    <div class="mt-2.5 flex items-center gap-4">
        <div class="{{ $box }} shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
            <template x-if="preview">
                <img :src="preview" alt="" class="h-full w-full object-cover">
            </template>
            @if ($currentUrl)
                <img x-show="!preview" src="{{ $currentUrl }}" alt="" class="h-full w-full object-cover">
            @endif
            <div x-show="!preview" @if ($currentUrl) style="display: none" @endif class="flex h-full w-full items-center justify-center text-slate-300 dark:text-slate-600">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m3 16 5-5 4 4 3-3 6 6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8" cy="9" r="1.5"/></svg>
            </div>
        </div>

        <div class="flex-1">
            <input id="{{ $name }}" name="{{ $name }}" type="file" accept="image/png,image/jpeg,image/webp"
                @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                @error($name) aria-invalid="true" @enderror
                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3.5 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-300 dark:file:bg-brand-950/50 dark:file:text-brand-400">
            <x-field-error :name="$name" />

            @if ($hasCurrent)
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="{{ $removeName }}" value="1"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-200 dark:border-slate-600 dark:bg-slate-800">
                    Remove current {{ strtolower($label) }}
                </label>
            @endif
        </div>
    </div>
</div>
