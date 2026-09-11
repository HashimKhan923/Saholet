@php
    $isProvider = $user->isProvider();
@endphp

@extends($isProvider ? 'layouts.provider' : 'layouts.app')

@section('title', __('messages.profile.title') . ' — ' . config('app.name'))
@section('page_title', __('messages.profile.title'))

@section('content')
@if ($isProvider)
    <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('messages.profile.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.profile.subtitle') }}</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
                {{ $errors->first() }}
            </div>
        @endif

        @include('profile._form')
    </div>
@else
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

        @include('profile._form')
    </section>
@endif
@endsection
