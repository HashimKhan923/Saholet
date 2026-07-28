@props(['name' => 'default'])

@php
    $isUploadedPath = $name && \Illuminate\Support\Str::contains($name, '/');
@endphp

@if ($isUploadedPath)
    {{-- Uploaded icons must fill whatever box the caller placed them in — ignore the small
    h-X w-X size class callers pass for the legacy inline-SVG glyphs below, since merging it
    here only left the image floating small inside its container instead of filling it. --}}
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($name) }}" alt=""
        {{ $attributes->except('class') }} class="h-full w-full rounded object-contain">
@else
    @php
        $icons = config('services_catalog.icons');
        $inner = $icons[$name] ?? $icons['default'];
    @endphp
    <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'h-6 w-6']) }}>{!! $inner !!}</svg>
@endif
