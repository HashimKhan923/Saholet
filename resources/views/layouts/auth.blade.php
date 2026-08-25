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

    <link rel="icon" type="image/png" href="{{ asset('images/Icon.png') . '?v=' . filemtime(public_path('images/Icon.png')) }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Icon.png') . '?v=' . filemtime(public_path('images/Icon.png')) }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if ($isRtl)
        <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-700 antialiased dark:bg-slate-950 dark:text-slate-300"
      x-data="{ role: '{{ old('role', 'consumer') }}' }">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Banner — role-aware, hidden on mobile so only the form shows. Stretches
             to match the form column's natural height, and shows the full image
             (object-contain, no cropping) framed with some breathing room. --}}
      <div class="hidden h-full items-center justify-center lg:flex dark:bg-slate-900">
    <div class="relative h-full w-full">
        <template x-for="src in ['CustomerBanner', 'ProviderBanner', 'JobSeekerBanner']" :key="src">
            <img :src="'/images/' + src + '.jpeg'"
                 x-show="({ consumer: 'CustomerBanner', provider: 'ProviderBanner', job_seeker: 'JobSeekerBanner' })[role] === src"
                 x-transition.opacity.duration.500ms
                 class="absolute left-1/2 top-1/2 h-[60rem] w-full -translate-x-1/2 -translate-y-1/2 rounded-2xl object-contain"
                 alt="">
        </template>
    </div>
</div>

        {{-- Form column --}}
        <div class="relative flex flex-col items-center justify-center px-4 py-12">
            {{-- Language switcher --}}
            <div class="absolute top-4 {{ $isRtl ? 'left-4' : 'right-4' }} flex items-center gap-1 text-sm">
                @foreach ($locales as $code => $meta)
                    <a href="{{ route('locale.switch', $code) }}" class="rounded-lg px-2.5 py-1.5 font-medium transition hover:bg-slate-50 dark:hover:bg-slate-800 {{ $code === $current ? 'text-brand-700 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $meta['native'] }}</a>
                @endforeach
            </div>

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="animate-fade-up mb-8" aria-label="{{ config('app.name') }} — home">
                <img src="{{ asset('images/Logo.png') }}?v={{ filemtime(public_path('images/Logo.png')) }}"
                     alt="{{ config('app.name') }} — سہولت آپ کے لیے"
                     class="h-20 w-auto"
                     width="267" height="80" decoding="async">
            </a>

            {{-- Card --}}
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>