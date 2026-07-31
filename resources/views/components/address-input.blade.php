@props(['name' => 'address', 'label' => 'Service address', 'value' => null, 'required' => true])

<div x-data="addressGeolocation()">
    <div class="flex items-center justify-between gap-3">
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
        <button type="button" @click="detect()" :disabled="locating"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 transition hover:text-brand-800 disabled:cursor-wait disabled:opacity-60 dark:text-brand-400">
            <svg x-show="!locating" viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg>
            <svg x-show="locating" x-cloak class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span x-show="!locating">Use my current location</span>
            <span x-show="locating" x-cloak>Locating…</span>
        </button>
    </div>

    @if (isset($savedAddresses) && $savedAddresses->isNotEmpty())
        <div class="mt-1.5 flex flex-wrap gap-2" x-data="{ selected: null }">
            @foreach ($savedAddresses as $saved)
                <button type="button"
                    data-address="{{ $saved->address }}"
                    data-lat="{{ $saved->latitude }}"
                    data-lng="{{ $saved->longitude }}"
                    @click="
                        selected = {{ $saved->id }};
                        $refs.address.value = $event.currentTarget.dataset.address;
                        $refs.lat.value = $event.currentTarget.dataset.lat || '';
                        $refs.lng.value = $event.currentTarget.dataset.lng || '';
                    "
                    :class="selected === {{ $saved->id }}
                        ? 'border-brand-600 bg-brand-600 text-white'
                        : 'border-slate-300 bg-white text-slate-700 hover:border-brand-300 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-700 dark:hover:bg-brand-950/30'"
                    class="inline-flex max-w-full items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition">
                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 21c-4-4-7-7.5-7-11a7 7 0 0 1 14 0c0 3.5-3 7-7 11z" stroke-linejoin="round"/></svg>
                    <span class="truncate">{{ $saved->label }}</span>
                    @if ($saved->is_default)
                        <span class="shrink-0 text-[10px] font-bold opacity-80">· Default</span>
                    @endif
                </button>
            @endforeach
        </div>
        <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">Tap a saved address to fill the fields below, or type a new one.</p>
    @endif

    <input id="{{ $name }}" x-ref="address" name="{{ $name }}" type="text" value="{{ $value }}" @if ($required) required @endif
        placeholder="House, street, area, city"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-800 dark:text-white
            @error($name) border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">

    <input type="hidden" name="latitude" x-ref="lat">
    <input type="hidden" name="longitude" x-ref="lng">

    <p x-show="error" x-cloak x-text="error" class="mt-1.5 text-xs text-red-600 dark:text-red-400"></p>
    <div id="{{ $name }}-error"><x-field-error :name="$name" /></div>
</div>
