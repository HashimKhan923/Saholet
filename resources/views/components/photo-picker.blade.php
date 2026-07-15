@props(['name' => 'photos', 'max' => 5])

<div x-data="photoPicker({{ $max }})">
    <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)"
        @click="$refs.input.click()"
        :class="dragging ? 'border-brand-400 bg-brand-50 dark:bg-brand-950/30' : 'border-slate-200 dark:border-slate-700'"
        class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-4 text-center transition hover:border-brand-300">
        <svg viewBox="0 0 24 24" class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 16V4M7 9l5-5 5 5M4 20h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Drag & drop photos, or click to choose</p>
        <p class="text-[11px] text-slate-400" x-text="`${photos.length}/{{ $max }} added — JPG or PNG, up to 5MB each`"></p>

        <input type="file" x-ref="input" name="{{ $name }}[]" accept="image/jpeg,image/png" multiple
            @change="handleChange($event)" @click.stop class="hidden">
    </div>

    <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5" x-show="photos.length > 0" x-cloak>
        <template x-for="(photo, index) in photos" :key="photo.url">
            <div class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                <img :src="photo.url" class="h-full w-full object-cover">
                <button type="button" @click.stop="remove(index)"
                    class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-slate-900/70 text-white opacity-0 transition group-hover:opacity-100"
                    aria-label="Remove photo">
                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                </button>
            </div>
        </template>
    </div>
</div>
