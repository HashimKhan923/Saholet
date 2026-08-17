@props(['lat' => null, 'lng' => null])

@php
    $activeGeofenceAreas = app(\App\Services\GeofenceService::class)->isEnabled()
        ? \App\Models\ServiceArea::active()->get()->filter->hasBoundary()->map(fn ($a) => ['name' => $a->name, 'boundary' => $a->boundary])->values()
        : collect();
@endphp

<div x-data="addressMapPicker({ key: @js(config('services.google_maps.key')), lat: @js($lat), lng: @js($lng), areas: @js($activeGeofenceAreas) })" class="mt-3">
    <div class="flex items-center justify-between gap-3">
        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Pin location on map</label>
        <button type="button" @click="locate()" :disabled="locating"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 transition hover:text-brand-800 disabled:cursor-wait disabled:opacity-60 dark:text-brand-400">
            <svg x-show="!locating" viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg>
            <svg x-show="locating" x-cloak class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span x-show="!locating">Use my current location</span>
            <span x-show="locating" x-cloak>Locating…</span>
        </button>
    </div>

    <div x-ref="search" class="address-autocomplete relative z-10 mt-1.5 rounded-lg border border-slate-300 shadow-sm dark:border-slate-700"></div>

    <div x-ref="map" class="mt-2 h-56 w-full rounded-lg border border-slate-300 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"></div>

    @if ($activeGeofenceAreas->isNotEmpty())
        <p class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
            <span class="inline-block h-2.5 w-2.5 rounded-sm border border-brand-600/60 bg-brand-600/10"></span>
            Shaded area is where we currently operate.
        </p>
    @endif

    <p x-show="!mapError && !outsideServiceArea" class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">Drag the pin, click the map, or search above to set the exact location.</p>
    <p x-show="mapError" x-cloak x-text="mapError" class="mt-1.5 text-xs text-red-600 dark:text-red-400"></p>
    <p x-show="outsideServiceArea" x-cloak class="mt-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        We don't operate in your "<span x-text="outsideAreaLabel"></span>" area yet — move the pin inside the shaded zone to save this address.
    </p>
</div>
