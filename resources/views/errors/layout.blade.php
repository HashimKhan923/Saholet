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
    <meta name="theme-color" content="#0d9488">
    <title>@yield('code') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased dark:bg-slate-950 dark:text-slate-300">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 text-center">
        <a href="/" class="mb-8 flex items-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-sm">
                <svg viewBox="0 0 24 24" class="h-5 w-5"><path d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-4v-5h-6v5H5a1 1 0 0 1-1-1v-8.5z" fill="currentColor"/></svg>
            </span>
            <span class="font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ config('app.name') }}</span>
        </a>

        <p class="font-display text-6xl font-extrabold text-brand-600">@yield('code')</p>
        <h1 class="mt-4 font-display text-2xl font-bold text-slate-900 dark:text-white">@yield('heading')</h1>
        <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-400">@yield('message')</p>

        <a href="/" class="mt-8 inline-flex items-center rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            Back to home
        </a>
    </div>
</body>
</html>