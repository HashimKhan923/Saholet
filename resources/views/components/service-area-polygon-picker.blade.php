@props(['boundary' => null])

<div x-data="serviceAreaPolygonPicker({ key: @js(config('services.google_maps.key')), boundary: @js($boundary) })" class="mt-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">
            Boundary <span class="text-red-500">*</span>
            <span x-show="pointCount > 0" x-text="'(' + pointCount + ' points)'" class="text-slate-400 dark:text-slate-500"></span>
        </label>
        <div class="flex items-center gap-3">
            <button type="button" @click="startDrawing()" x-show="!drawing && pointCount === 0"
                class="rounded-lg border border-brand-600 px-3 py-1.5 text-xs font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50 dark:border-brand-500 dark:text-brand-400 dark:hover:bg-brand-950/40">
                Draw boundary
            </button>
            <button type="button" @click="finishDrawing()" x-show="drawing" x-cloak
                class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">
                Finish (<span x-text="pointCount"></span> points)
            </button>
            <button type="button" @click="clearPolygon()" x-show="pointCount > 0 || drawing"
                class="text-xs font-semibold text-red-600 transition hover:text-red-700 dark:text-red-400">
                Clear polygon
            </button>
        </div>
    </div>

    <div x-ref="search" class="address-autocomplete relative z-10 mt-1.5 rounded-lg border border-slate-300 shadow-sm dark:border-slate-700"></div>

    <div x-ref="map" class="mt-2 h-128 w-full rounded-lg border border-slate-300 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"></div>

    <input type="hidden" name="boundary" value="{{ $boundary ? json_encode($boundary) : '' }}">

    <p x-show="!mapError" class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">
        <template x-if="drawing">
            <span>Click the map to place each point of the boundary, then click "Finish" (at least 3 points).</span>
        </template>
        <template x-if="!drawing">
            <span>Click "Draw boundary" to trace the served area on the map. Once drawn, drag any point to adjust it. A location outside every drawn boundary is always treated as outside our service area.</span>
        </template>
    </p>
    <p x-show="mapError" x-cloak x-text="mapError" class="mt-1.5 text-xs text-red-600 dark:text-red-400"></p>
</div>
