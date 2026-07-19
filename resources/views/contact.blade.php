@extends('layouts.app')

@section('title', __('messages.contact.title') . ' — ' . config('app.name'))

@section('content')
@php
    $isUrdu = app()->getLocale() === 'ur';
@endphp

<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8 {{ $isUrdu ? 'text-right' : '' }}">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.title') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400 {{ $isUrdu ? 'font-urdu text-lg' : '' }}">{{ __('messages.contact.subtitle') }}</p>
    </div>
</section>

<section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('contact.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.name') }}</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                            @error('name') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <x-field-error name="name" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            @error('email') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                @error('email') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <x-field-error name="email" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.phone_optional') }}</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                            @error('phone') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                @error('phone') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <x-field-error name="phone" />
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.subject_optional') }}</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}"
                            @error('subject') aria-invalid="true" @enderror
                            class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                                @error('subject') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                        <x-field-error name="subject" />
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.message') }}</label>
                    <textarea id="message" name="message" rows="5" required
                        @error('message') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                            @error('message') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('message') }}</textarea>
                    <x-field-error name="message" />
                </div>

                <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 sm:w-auto {{ $isUrdu ? 'font-urdu' : '' }}">
                    {{ __('messages.contact.send') }}
                </button>
            </form>
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_title') }}</h2>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <p class="text-xs text-slate-400 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_email') }}</p>
                            <a href="mailto:info@sahoulat.com" class="font-medium text-slate-800 transition hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">info@sahoulat.com</a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <p class="text-xs text-slate-400 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_phone') }}</p>
                            <a href="https://wa.me/923313578446" class="font-medium text-slate-800 transition hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">+92 331 3578446</a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="11" r="3"/><path d="M12 2c4 0 7 3 7 7 0 4.5-7 13-7 13S5 13.5 5 9c0-4 3-7 7-7z" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <p class="text-xs text-slate-400 {{ $isUrdu ? 'font-urdu' : '' }}">{{ __('messages.contact.direct_address') }}</p>
                            <a href="https://www.google.com/maps/place/Sahoulat/@25.0297021,67.3047431,1010m" class="font-medium text-slate-800 transition hover:text-brand-700 dark:text-slate-200 dark:hover:text-brand-400">Bahria Town Karachi, Pakistan</a>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
