@props(['label' => 'Service address'])

@php
    $savedAddresses = auth()->user()->addresses ?? collect();
    $preselected = old('address')
        ? $savedAddresses->first(fn ($a) => $a->address === old('address'))
        : $savedAddresses->firstWhere('is_default', true);
@endphp

<div x-data="{ selected: {{ $preselected?->id ?? 'null' }} }">
    <div class="flex items-center justify-between gap-3">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
        <a href="{{ route('consumer.addresses.index') }}" target="_blank"
            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 transition hover:text-brand-800 dark:text-brand-400">
            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            Add address
        </a>
    </div>

    @if ($savedAddresses->isEmpty())
        <div class="mt-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No saved addresses yet</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Click "Add address" above to pin one on the map (opens in a new tab), then come back and refresh this page.</p>
        </div>
    @else
        <div class="mt-2 space-y-2">
            @foreach ($savedAddresses as $saved)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition"
                    :class="selected === {{ $saved->id }} ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="saved_address_id" value="{{ $saved->id }}" required
                        @checked($preselected?->id === $saved->id)
                        @click="
                            selected = {{ $saved->id }};
                            $refs.address.value = @js($saved->address);
                            $refs.city.value = @js($saved->city);
                            $refs.latitude.value = @js($saved->latitude !== null ? (string) $saved->latitude : '');
                            $refs.longitude.value = @js($saved->longitude !== null ? (string) $saved->longitude : '');
                        "
                        class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span class="text-sm">
                        <span class="flex items-center gap-2">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $saved->label }}</span>
                            @if ($saved->is_default)
                                <span class="text-[10px] font-bold uppercase tracking-wide text-brand-600 dark:text-brand-400">Default</span>
                            @endif
                        </span>
                        <span class="block text-slate-500 dark:text-slate-400">{{ $saved->address }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    @endif

    <input type="hidden" name="address" x-ref="address" value="{{ $preselected?->address ?? old('address') }}">
    <input type="hidden" name="city" x-ref="city" value="{{ $preselected?->city ?? old('city') }}">
    <input type="hidden" name="latitude" x-ref="latitude" value="{{ $preselected?->latitude ?? old('latitude') }}">
    <input type="hidden" name="longitude" x-ref="longitude" value="{{ $preselected?->longitude ?? old('longitude') }}">
    <x-field-error name="address" />
    <x-field-error name="city" />
</div>
