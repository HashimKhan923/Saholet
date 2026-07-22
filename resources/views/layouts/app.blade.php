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
    <meta name="theme-color" content="#2e783d">

    {{-- Apply saved theme before paint to avoid a flash of the wrong theme --}}
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var dark = stored === 'dark';
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    <meta name="description" content="@yield('meta_description', 'On-demand home services across Pakistan — AC repair, plumbing, electrical, cleaning and more. Verified professionals, instant booking, secure payments.')">

    <title>@yield('title', config('app.name'))</title>

    {{-- SEO / OpenGraph --}}
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', 'On-demand home services across Pakistan — verified professionals, instant booking, secure payments.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/Logo.png') }}?v={{ filemtime(public_path('images/Logo.png')) }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', config('app.name'))">
    <meta name="twitter:description" content="@yield('meta_description', 'On-demand home services across Pakistan.')">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/Icon.png') . '?v=' . filemtime(public_path('images/Icon.png')) }}">

    {{-- PWA --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Icon.png') . '?v=' . filemtime(public_path('images/Icon.png')) }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if ($isRtl)
        <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Structured data (JSON-LD) — pushed by individual pages for rich search results --}}
    @stack('jsonld')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased dark:bg-slate-950 dark:text-slate-300">

    {{-- ========================================================= Topbar --}}
    <div class="sticky top-0 z-40 bg-brand-700 text-xs text-white dark:bg-brand-950">
        <div class="mx-auto flex h-9 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            {{-- Contact --}}
            <div class="flex items-center gap-4 sm:gap-5">
                <a href="https://wa.me/923313578446" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1.5 opacity-90 transition hover:opacity-100">
                    <svg viewBox="0 0 24 24" class="h-3 w-3 fill-current"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    <span class="hidden font-medium sm:inline">+92 331 3578446</span>
                </a>
                <a href="mailto:info@sahoulat.com" class="flex items-center gap-1.5 opacity-90 transition hover:opacity-100">
                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="hidden sm:inline">info@sahoulat.com</span>
                </a>
            </div>

            {{-- Socials + language --}}
            <div class="flex items-center gap-3">
                <a href="https://www.facebook.com/people/Sahoulat/61591342542475/" target="_blank" rel="noopener" aria-label="Facebook" class="grid h-6 w-6 place-items-center rounded bg-white/15 transition-colors hover:bg-white/30">
                    <svg viewBox="0 0 24 24" class="h-3 w-3 fill-current"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="https://www.instagram.com/sahoulatpk/" target="_blank" rel="noopener" aria-label="Instagram" class="grid h-6 w-6 place-items-center rounded bg-white/15 transition-colors hover:bg-white/30">
                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="https://wa.me/923313578446" target="_blank" rel="noopener" aria-label="WhatsApp" class="grid h-6 w-6 place-items-center rounded bg-white/15 transition-colors hover:bg-white/30">
                    <svg viewBox="0 0 24 24" class="h-3 w-3 fill-current"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                </a>
                <div class="mx-1 h-4 w-px bg-white/20"></div>
                @foreach ($locales as $code => $meta)
                    <a href="{{ route('locale.switch', $code) }}"
                       class="rounded px-2 py-0.5 text-xs font-semibold transition-all {{ $code === $current ? 'bg-white text-brand-700' : 'text-white/70 hover:text-white' }}">
                        {{ $meta['native'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ========================================================= Header --}}
    <header x-data="{
            open: false,
            dark: document.documentElement.classList.contains('dark'),
            profileOpen: false,
            toggleTheme() {
                this.dark = ! this.dark;
                document.documentElement.classList.toggle('dark', this.dark);
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            },
        }"
        class="sticky top-9 z-40 border-b border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:h-[5.5rem] sm:px-6 lg:px-8">

            {{-- Brand / logo --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="{{ config('app.name') }} — home">
                <span class="rounded-2xl p-1  transition  dark:ring-slate-700">
                    <img src="{{ asset('images/Logo.png') }}?v={{ filemtime(public_path('images/Logo.png')) }}"
                         alt="{{ config('app.name') }} — سہولت آپ کے لیے"
                         class="h-16 w-auto sm:h-16 dark:hidden"
                         width="107" height="64" decoding="async">
                    <img src="{{ asset('images/WhiteLogo.png') }}?v={{ filemtime(public_path('images/WhiteLogo.png')) }}"
                         alt="{{ config('app.name') }} — سہولت آپ کے لیے"
                         class="hidden h-16 w-auto sm:h-16 dark:block"
                         width="107" height="64" decoding="async">
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden items-center gap-6 md:flex">
                <a href="{{ route('services.index') }}" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 {{ request()->routeIs('services.*') ? '!text-brand-700 dark:!text-brand-400' : '' }}">{{ __('messages.nav.services') }}</a>
                <a href="{{ route('providers.index') }}" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 {{ request()->routeIs('providers.*') ? '!text-brand-700 dark:!text-brand-400' : '' }}">{{ __('messages.providers.nav_label') }}</a>
                <a href="{{ route('careers.index') }}" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 {{ request()->routeIs('careers.*') ? '!text-brand-700 dark:!text-brand-400' : '' }}">{{ __('messages.nav.careers') }}</a>
                <a href="{{ route('subscription-plans.index') }}" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 {{ request()->routeIs('subscription-plans.*') ? '!text-brand-700 dark:!text-brand-400' : '' }}">{{ __('messages.nav.plans') }}</a>
                @if (request()->routeIs('home'))
                    <a href="#how" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400">{{ __('messages.nav.how') }}</a>
                    <a href="#why-us" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400">{{ __('messages.landing.why_eyebrow') }}</a>
                    <a href="#faq" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400">{{ __('messages.landing.faq_eyebrow') }}</a>
                @endif
                <a href="{{ route('contact') }}" class="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 {{ request()->routeIs('contact') ? '!text-brand-700 dark:!text-brand-400' : '' }}">{{ __('messages.nav.contact') }}</a>
            </nav>

            {{-- Right side --}}
            <div class="hidden items-center gap-3 md:flex">
                {{-- Theme toggle --}}
                <x-theme-toggle />

                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border-2 border-brand-600 px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-600 hover:text-white dark:text-brand-400 dark:hover:text-white">{{ __('messages.nav.login') }}</a>
                    <a href="{{ route('register') }}" class="btn-shine inline-flex items-center justify-center rounded-xl border-2 border-brand-600 bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        {{ __('messages.nav.signup') }}
                    </a>
                @endguest
                @auth
                    <x-notification-bell />

                    {{-- Profile dropdown --}}
                    <div class="relative" x-data="{ profileOpen: false }">
                        <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false"
                            class="inline-flex items-center gap-2 rounded-xl px-2.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-7 w-7 rounded-lg object-cover">
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-xs font-bold text-white">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                            @endif
                            <span class="hidden lg:inline">{{ auth()->user()->name }}</span>
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div x-show="profileOpen" x-cloak x-transition
                            class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                {{ __('messages.nav.dashboard') }}
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0" stroke-linecap="round"/></svg>
                                {{ __('messages.profile.title') }}
                            </a>
                            <div class="my-1.5 border-t border-slate-100 dark:border-slate-700"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('messages.nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            {{-- Mobile toggle --}}
            <button @click="open = !open" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 md:hidden" aria-label="Menu">
                <svg x-show="!open" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
                <svg x-show="open" x-cloak viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 md:hidden">
            <nav class="space-y-1 px-4 py-3">
                <a href="{{ route('services.index') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.nav.services') }}</a>
                <a href="{{ route('providers.index') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.providers.nav_label') }}</a>
                <a href="{{ route('careers.index') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.nav.careers') }}</a>
                <a href="{{ route('subscription-plans.index') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.nav.plans') }}</a>
                @if (request()->routeIs('home'))
                    <a href="#how" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.nav.how') }}</a>
                    <a href="#why-us" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.landing.why_eyebrow') }}</a>
                    <a href="#faq" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.landing.faq_eyebrow') }}</a>
                @endif
                <a href="{{ route('contact') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.nav.contact') }}</a>

                <x-theme-toggle mobile />

                @guest
                    <a href="{{ route('login') }}" @click="open = false" class="block rounded-lg border-2 border-brand-600 px-3 py-2 text-center text-sm font-semibold text-brand-700 transition hover:bg-brand-600 hover:text-white dark:text-brand-400 dark:hover:text-white">{{ __('messages.nav.login') }}</a>
                    <a href="{{ route('register') }}" @click="open = false" class="mt-1 block rounded-lg border-2 border-brand-600 bg-brand-600 px-3 py-2 text-center text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('messages.nav.signup') }}</a>
                @endguest
                @auth
                    <a href="{{ route('notifications.index') }}" @click="open = false" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                        <span>{{ __('messages.nav.notifications') }}</span>
                        <span x-show="$store.notifications.unreadCount > 0" x-cloak
                            class="inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                            x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"></span>
                    </a>
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.nav.dashboard') }}</a>
                    <a href="{{ route('profile.edit') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('messages.profile.title') }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('messages.nav.logout') }}</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Flash messages --}}
    @if (session('success') || session('error'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- ========================================================= Footer --}}
    <footer class="border-t border-slate-200 bg-black dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto max-w-7xl px-4 pt-10 pb-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-5">

                {{-- Brand column --}}
                <div class="lg:col-span-2">
                    <div class="inline-flex rounded-2xl p-2 ">
                        <img src="{{ asset('images/WhiteLogo.png') }}?v={{ filemtime(public_path('images/WhiteLogo.png')) }}"
                             alt="{{ config('app.name') }} — سہولت آپ کے لیے"
                             class="h-16 w-auto"
                             width="187" height="56" loading="lazy" decoding="async">
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white dark:text-slate-400">{{ __('messages.footer.blurb') }}</p>

                    {{-- Social links --}}
                    <div class="mt-5 flex items-center gap-3">
                        <a href="https://www.facebook.com/people/Sahoulat/61591342542475/" aria-label="Facebook" class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-brand-950/50 dark:hover:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/sahoulatpk/" aria-label="Instagram" class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-brand-950/50 dark:hover:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
                        </a>
                        <a href="https://wa.me/923313578446" aria-label="WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-brand-950/50 dark:hover:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Quick links --}}
                <div>
                    <p class="text-sm font-bold uppercase text-white dark:text-white">{{ __('messages.nav.services') }}</p>
                    <nav class="mt-4 space-y-2.5">
                        <a href="{{ route('services.index') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.landing.browse_all') }}</a>
                        <a href="{{ route('providers.index') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.providers.nav_label') }}</a>
                        <a href="{{ route('careers.index') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.nav.careers') }}</a>
                        <a href="{{ route('subscription-plans.index') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.nav.plans') }}</a>
                        @guest
                            <a href="{{ route('register') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.nav.signup') }}</a>
                        @endguest
                    </nav>
                </div>

                {{-- Company --}}
                <div>
                    <p class="text-sm font-bold uppercase text-white dark:text-white">{{ __('messages.footer.col_company') }}</p>
                    <nav class="mt-4 space-y-2.5">
                        <a href="{{ route('home') }}#how" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.nav.how') }}</a>
                        <a href="{{ route('home') }}#why-us" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.landing.why_eyebrow') }}</a>
                        <a href="{{ route('home') }}#faq" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">{{ __('messages.landing.faq_eyebrow') }}</a>
                        <a href="{{ route('legal.privacy') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">Privacy Policy</a>
                        <a href="{{ route('legal.terms') }}" class="block text-sm text-white transition hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">Terms &amp; Conditions</a>
                    </nav>
                </div>

                {{-- Contact --}}
                <div>
                                        <p class="text-sm font-bold uppercase text-white dark:text-white">
                                           {{ __('messages.footer.get_in_touch') }}</p>
                    <div class="mt-4 space-y-2.5 text-sm text-white dark:text-slate-400">
                        <a href="mailto:info@sahoulat.com" class="block transition hover:text-brand-700 dark:hover:text-brand-400">info@sahoulat.com</a>
                        <a href="https://wa.me/923313578446" class="block transition hover:text-brand-700 dark:hover:text-brand-400">+92 331 3578446</a>
                        <a href="https://www.google.com/maps/place/Sahoulat/@25.0297021,67.3047431,1010m/data=!3m2!1e3!4b1!4m6!3m5!1s0x3eb34b7ea3c7553b:0x7deaeb9437cf9ae3!8m2!3d25.0297021!4d67.307318!16s%2Fg%2F11zgt5m2j_?entry=ttu&g_ep=EgoyMDI2MDcxNC4wIKXMDSoASAFQAw%3D%3D" class="block transition hover:text-brand-700 dark:hover:text-brand-400">
                            Bahria Town Karachi, Pakistan
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-4 text-center text-xs text-white dark:border-slate-800 md:flex-row">

    <div>
        &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.footer.rights') }}
    </div>

    <div class="flex flex-wrap items-center justify-center gap-4">
        <a
            href="{{ route('legal.privacy') }}"
            class="transition hover:text-brand-300"
        >
            Privacy Policy
        </a>


        <a
            href="{{ route('legal.terms') }}"
            class="transition hover:text-brand-300"
        >
            Terms &amp; Conditions
        </a>
    </div>

</div>
    </footer>

    <x-toast-stack />

</body>
</html>