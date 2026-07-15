@extends('layouts.auth')

@section('title', __('messages.reset.title') . ' — ' . config('app.name'))

@section('content')
<div class="animate-fade-up rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('messages.reset.title') }}</h1>
    <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.reset.subtitle') }}</p>

    @if (session('success'))
        <div class="mt-5 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>

        <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            {{ __('messages.reset.send_link') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">{{ __('messages.reset.back_to_login') }}</a>
    </p>
</div>
@endsection