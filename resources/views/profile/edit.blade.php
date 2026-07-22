@extends('layouts.app')

@section('title', __('messages.profile.title') . ' — ' . config('app.name'))

@section('content')

{{-- Page header --}}
<section class="relative overflow-hidden border-b border-slate-100 dark:border-slate-800">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-slate-50 dark:from-brand-950 dark:to-slate-950"></div>
    <div class="absolute inset-0 -z-10 bg-dot-grid opacity-50"></div>
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="animate-fade-up font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('messages.profile.title') }}</h1>
        <p class="animate-fade-up mt-2 text-slate-600 dark:text-slate-400">{{ __('messages.profile.subtitle') }}</p>
    </div>
</section>

<section class="mx-auto max-w-3xl space-y-8 px-4 py-12 sm:px-6 lg:px-8">

    {{-- Errors --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Personal details card --}}
    <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-4">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-14 w-14 rounded-2xl object-cover shadow-md shadow-brand-600/25">
            @else
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 font-display text-xl font-extrabold text-white shadow-md shadow-brand-600/25">
                    {{ mb_substr($user->name, 0, 1) }}
                </span>
            @endif
            <div>
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.profile.details') }}</h2>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $user->role }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <x-admin.image-upload-field
                name="avatar"
                label="Profile photo"
                help="Square photo works best — recommended at least 400×400px (JPG, PNG or WebP, up to 4MB)."
                :current-url="$user->avatar_url"
                :has-current="(bool) $user->avatar_url"
                box="h-20 w-20"
            />

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.auth.full_name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.auth.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.auth.phone') }}</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" placeholder="03XX XXXXXXX"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>

            <button type="submit" class="btn-shine rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                {{ __('messages.profile.save') }}
            </button>
        </form>
    </div>

    {{-- Change password card --}}
    <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900" style="--reveal-delay: 100ms">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.profile.password') }}</h2>

        <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.profile.current_password') }}</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.profile.new_password') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <p class="mt-1.5 text-xs text-slate-400">{{ __('messages.auth.min_chars') }}</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.profile.confirm_password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>

            <button type="submit" class="rounded-xl border-2 border-brand-600 px-5 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-600 hover:text-white dark:text-brand-400 dark:hover:text-white">
                {{ __('messages.profile.update_password') }}
            </button>
        </form>
    </div>
</section>

@endsection