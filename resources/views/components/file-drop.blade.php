@props(['name' => 'file', 'accept' => '.jpg,.jpeg,.png,.pdf', 'required' => true])

<div x-data="fileDrop()" class="flex-1">
    <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)"
        @click="$refs.input.click()"
        :class="dragging ? 'border-brand-400 bg-brand-50 dark:bg-brand-950/30' : 'border-slate-200 dark:border-slate-700'"
        class="flex cursor-pointer items-center gap-3 rounded-lg border-2 border-dashed p-2.5 transition hover:border-brand-300">
        <template x-if="previewUrl">
            <img :src="previewUrl" class="h-9 w-9 flex-shrink-0 rounded object-cover">
        </template>
        <template x-if="!previewUrl && isPdf">
            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded bg-red-50 text-red-500 dark:bg-red-950/40 dark:text-red-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" stroke-linejoin="round"/><path d="M9 13h6M9 17h4" stroke-linecap="round"/></svg>
            </span>
        </template>
        <template x-if="!previewUrl && !isPdf">
            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4M7 9l5-5 5 5M4 20h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </template>

        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-medium text-slate-700 dark:text-slate-300" x-text="fileName || 'Drag & drop or click to choose a file'"></p>
            <p class="text-[11px] text-slate-400">JPG, PNG or PDF</p>
        </div>

        <input type="file" x-ref="input" name="{{ $name }}" accept="{{ $accept }}" @if ($required) required @endif
            @change="handleChange($event)" @click.stop class="hidden">
    </div>
</div>
