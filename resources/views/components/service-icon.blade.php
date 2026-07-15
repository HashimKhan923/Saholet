@props(['name' => 'default'])

@php
    $icons = config('services_catalog.icons');
    $inner = $icons[$name] ?? $icons['default'];
@endphp

<svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'h-6 w-6']) }}>{!! $inner !!}</svg>