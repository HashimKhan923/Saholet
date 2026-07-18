@php
    $locales = config('locales.supported');
    $current = app()->getLocale();
    $isRtl = (bool) ($locales[$current]['rtl'] ?? false);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $current) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1a7a35">
    <title>@yield('title', config('app.name'))</title>

    <link rel="manifest" href="{{ route('pwa.manifest') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if ($isRtl)
        <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased dark:bg-slate-950 dark:text-slate-300">
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
        {{-- Ambient backdrop --}}
        <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
        <div class="absolute inset-0 -z-10 bg-dot-grid opacity-40"></div>

        {{-- Language switcher --}}
        <div class="absolute top-4 {{ $isRtl ? 'left-4' : 'right-4' }} flex items-center gap-1 text-sm">
            @foreach ($locales as $code => $meta)
                <a href="{{ route('locale.switch', $code) }}" class="rounded-lg px-2.5 py-1.5 font-medium transition hover:bg-white dark:hover:bg-slate-800 {{ $code === $current ? 'text-brand-700 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $meta['native'] }}</a>
            @endforeach
        </div>

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="animate-fade-up mb-8" aria-label="{{ config('app.name') }} — home">
            <img src="{{ asset('images/Logo.png') }}"
                 alt="{{ config('app.name') }} — سہولت آپ کے لیے"
                 class="h-20 w-auto"
                 width="267" height="80" decoding="async">
        </a>

        {{-- Card --}}
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
</body>
</html>