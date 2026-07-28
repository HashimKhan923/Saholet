@props(['name' => 'default'])

@php
    $isUploadedPath = $name && \Illuminate\Support\Str::contains($name, '/');
@endphp

@if ($isUploadedPath)
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($name) }}" alt=""
        {{ $attributes->merge(['class' => 'h-6 w-6 rounded object-contain']) }}>
@else
    @php
        $icons = config('services_catalog.icons');
        $inner = $icons[$name] ?? $icons['default'];
    @endphp
    <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'h-6 w-6']) }}>{!! $inner !!}</svg>
@endif
