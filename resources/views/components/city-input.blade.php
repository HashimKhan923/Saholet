@props(['name' => 'city', 'label' => 'City', 'value' => null, 'required' => true, 'cities' => []])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
    <input id="{{ $name }}" list="{{ $name }}-list" name="{{ $name }}" type="text" value="{{ $value }}" @if ($required) required @endif
        placeholder="e.g. Karachi" autocomplete="off"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-800 dark:text-white
            @error($name) border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
    <datalist id="{{ $name }}-list">
        @foreach ($cities as $city)
            <option value="{{ $city }}"></option>
        @endforeach
    </datalist>
    <div id="{{ $name }}-error"><x-field-error :name="$name" /></div>
</div>
