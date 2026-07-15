@extends('layouts.auth')

@section('title', __('messages.auth.welcome_back') . ' — ' . config('app.name'))

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">{{ __('messages.auth.welcome_back') }}</h1>
    <p class="mt-1.5 text-sm text-slate-500">{{ __('messages.auth.login_sub') }}</p>

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                {{ __('messages.auth.remember') }}
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800 dark:text-brand-400">{{ __('messages.reset.forgot') }}</a>
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            {{ __('messages.nav.login') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        {{ __('messages.auth.no_account') }}
        <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:text-brand-800">{{ __('messages.nav.signup') }}</a>
    </p>
</div>
@endsection