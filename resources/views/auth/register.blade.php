@extends('layouts.auth')

@section('title', __('messages.auth.create_account') . ' — ' . config('app.name'))

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" x-data="{ role: '{{ old('role', 'consumer') }}' }">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">{{ __('messages.auth.create_account') }}</h1>
    <p class="mt-1.5 text-sm text-slate-500">{{ __('messages.auth.register_sub') }}</p>

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="referral_code" value="{{ old('referral_code', $ref ?? '') }}">
        @if (!empty($ref))
            <div class="rounded-lg border border-brand-200 bg-brand-50 px-3.5 py-2.5 text-xs font-medium text-brand-700">
                {{ __('messages.auth.referral_applied') }}
            </div>
        @endif

        <div>
            <span class="block text-sm font-medium text-slate-700">{{ __('messages.auth.i_want_to') }}</span>
            <input type="hidden" name="role" :value="role">
            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <button type="button" @click="role = 'consumer'"
                    :class="role === 'consumer' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200' : 'border-slate-200 hover:border-slate-300'"
                    class="rounded-xl border p-3 text-start transition">
                    <span class="block text-sm font-semibold text-slate-900">{{ __('messages.auth.book_services') }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ __('messages.auth.im_customer') }}</span>
                </button>
                <button type="button" @click="role = 'provider'"
                    :class="role === 'provider' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200' : 'border-slate-200 hover:border-slate-300'"
                    class="rounded-xl border p-3 text-start transition">
                    <span class="block text-sm font-semibold text-slate-900">{{ __('messages.auth.offer_services') }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ __('messages.auth.im_pro') }}</span>
                </button>
                <button type="button" @click="role = 'job_seeker'"
                    :class="role === 'job_seeker' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200' : 'border-slate-200 hover:border-slate-300'"
                    class="rounded-xl border p-3 text-start transition">
                    <span class="block text-sm font-semibold text-slate-900">{{ __('messages.auth.find_job') }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ __('messages.auth.im_job_seeker') }}</span>
                </button>
            </div>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.full_name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.phone') }}</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+92 3XX XXXXXXX"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
            <p class="mt-1 text-xs text-slate-400">{{ __('messages.auth.min_chars') }}</p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.confirm_password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            {{ __('messages.auth.create_btn') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        {{ __('messages.auth.have_account') }}
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">{{ __('messages.nav.login') }}</a>
    </p>
</div>
@endsection