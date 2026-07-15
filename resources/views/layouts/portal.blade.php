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

    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    <title>@yield('title', config('app.name'))</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" href="{{ asset('images/sahoulat-logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @if ($isRtl)
        <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased dark:bg-slate-950 dark:text-slate-300"
    x-data="{
        sidebarOpen: false,
        dark: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.dark = ! this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        },
    }">

    <div class="flex min-h-screen">
        {{-- ===== Desktop sidebar ===== --}}
        <aside class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-e lg:border-slate-200/80 lg:bg-white dark:lg:border-slate-800/80 dark:lg:bg-slate-900">

            {{-- Brand --}}
            <div class="relative flex h-20 shrink-0 items-center overflow-hidden border-b border-slate-100 px-5 dark:border-slate-800">
                <div class="pointer-events-none absolute -start-10 -top-14 h-32 w-32 rounded-full bg-brand-500/10 blur-2xl"></div>
                <a href="{{ route('home') }}" class="relative flex shrink-0 items-center" aria-label="{{ config('app.name') }} — home">
                    <span class="rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700">
                        <img src="{{ asset('images/sahoulat-logo.jpg') }}"
                             alt="{{ config('app.name') }}"
                             class="h-12 w-auto" width="80" height="48" decoding="async">
                    </span>
                </a>
            </div>

            {{-- Role badge --}}
            <div class="px-5 pt-5 pb-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-brand-600 to-brand-500 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white shadow-sm shadow-brand-900/10">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping-ring rounded-full bg-white"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-white"></span>
                    </span>
                    @yield('portal_label')
                </span>
            </div>

            {{-- Nav links --}}
            <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-3 pb-4">
                @yield('nav')
            </nav>

            {{-- Bottom user card --}}
            <div class="border-t border-slate-100 p-3 dark:border-slate-800">
                <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-slate-50 dark:hover:bg-slate-800">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-900">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
                    </div>
                    <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500 rtl:rotate-180 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </aside>

        {{-- ===== Mobile sidebar ===== --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity @click="sidebarOpen = false"></div>
            <aside x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative flex h-full w-72 flex-col bg-white dark:bg-slate-900">

                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <span class="rounded-xl bg-white p-1 ring-1 ring-slate-200/70 dark:ring-slate-700">
                            <img src="{{ asset('images/sahoulat-logo.jpg') }}" alt="{{ config('app.name') }}" class="h-10 w-auto" width="67" height="40" decoding="async">
                        </span>
                    </a>
                    <button @click="sidebarOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800" aria-label="Close menu">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <div class="px-5 pt-4 pb-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-brand-600 to-brand-500 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white shadow-sm shadow-brand-900/10">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping-ring rounded-full bg-white"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-white"></span>
                        </span>
                        @yield('portal_label')
                    </span>
                </div>

                <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-3 pb-4">
                    @yield('nav')
                </nav>

                <div class="border-t border-slate-100 p-3 dark:border-slate-800">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-900">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400">{{ __('messages.profile.title') }}</p>
                        </div>
                    </a>
                </div>
            </aside>
        </div>

        {{-- ===== Main content area ===== --}}
        <div class="flex flex-1 flex-col lg:ps-64">
            {{-- Topbar --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/80 px-4 shadow-sm shadow-slate-900/[0.02] backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/80 sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden" aria-label="Open menu">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg>
                    </button>
                    <h1 class="font-display text-lg font-bold text-slate-900 dark:text-white">
                        @hasSection('page_title')
                            @yield('page_title')
                        @else
                            @yield('portal_label')
                        @endif
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Language switcher --}}
                    <div x-data="{ langOpen: false }" class="relative hidden sm:block">
                        <button @click="langOpen = !langOpen" @click.outside="langOpen = false"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18" stroke-linecap="round"/></svg>
                            <span>{{ $locales[$current]['native'] ?? strtoupper($current) }}</span>
                        </button>
                        <div x-show="langOpen" x-cloak x-transition
                            class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-2 w-36 rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                            @foreach ($locales as $code => $meta)
                                <a href="{{ route('locale.switch', $code) }}"
                                   class="block px-3 py-2 text-sm transition hover:bg-slate-50 dark:hover:bg-slate-700 {{ $code === $current ? 'font-semibold text-brand-700 dark:text-brand-400' : 'text-slate-600 dark:text-slate-300' }}">
                                    {{ $meta['native'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <x-theme-toggle />
                    <x-notification-bell />

                    {{-- User dropdown --}}
                    <div x-data="{ userOpen: false }" class="relative">
                        <button @click="userOpen = ! userOpen" @click.outside="userOpen = false"
                            class="flex items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-600 text-xs font-bold text-white shadow-sm">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:block">{{ auth()->user()->name }}</span>
                            <svg viewBox="0 0 24 24" class="hidden h-4 w-4 text-slate-400 sm:block" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div x-show="userOpen" x-cloak x-transition
                            class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-2 w-52 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                            <div class="border-b border-slate-100 px-3.5 py-2.5 dark:border-slate-700">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400">@yield('portal_label')</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0" stroke-linecap="round"/></svg>
                                {{ __('messages.profile.title') }}
                            </a>
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-5h6v5h3a1 1 0 001-1V10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Visit main site
                            </a>
                            <div class="my-1.5 border-t border-slate-100 dark:border-slate-700"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('messages.nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            @if (session('success') || session('error'))
                <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6">
                    @if (session('success'))
                        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
                    @endif
                </div>
            @endif

            <main class="flex-1 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    <x-toast-stack />

</body>
</html>