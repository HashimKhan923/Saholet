@extends('layouts.auth')

@section('title', __('messages.auth.create_account') . ' — ' . config('app.name'))

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
    x-data="registerOtp({
        firebaseConfig: {
            apiKey: @js(config('services.firebase_web.api_key')),
            authDomain: @js(config('services.firebase_web.auth_domain')),
            projectId: @js(config('services.firebase_web.project_id')),
            storageBucket: @js(config('services.firebase_web.storage_bucket')),
            messagingSenderId: @js(config('services.firebase_web.messaging_sender_id')),
            appId: @js(config('services.firebase_web.app_id')),
        },
        checkUrl: @js(route('register.check')),
        registerUrl: @js(route('register')),
        verifySubTemplate: @js(__('messages.auth.verify_phone_sub')),
        initialRole: @js(old('role', 'consumer')),
    })"
>
    <template x-if="step === 'form'">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">{{ __('messages.auth.create_account') }}</h1>
            <p class="mt-1.5 text-sm text-slate-500">{{ __('messages.auth.register_sub') }}</p>
        </div>
    </template>
    <template x-if="step === 'otp'">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">{{ __('messages.auth.verify_phone_title') }}</h1>
            <p class="mt-1.5 text-sm text-slate-500" x-text="verifySubTemplate.replace(':phone', phoneDisplay)"></p>
        </div>
    </template>

    <div x-show="generalError" x-cloak class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="generalError"></div>

    {{-- Invisible reCAPTCHA container required by Firebase — stays mounted across both steps. --}}
    <div x-ref="recaptcha"></div>

    <div x-show="step === 'form'">
        <form x-ref="form" @submit.prevent="submitForm()" class="mt-6 space-y-5">
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
                <p x-show="fieldError('name')" x-cloak x-text="fieldError('name')" class="mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                <p x-show="fieldError('email')" x-cloak x-text="fieldError('email')" class="mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.phone') }}</label>
                <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone') }}" required autocomplete="tel" placeholder="0300-1234567"
                    data-mask="phone-pk" maxlength="12"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                <p x-show="fieldError('phone')" x-cloak x-text="fieldError('phone')" class="mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.password') }}</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                    <x-password-toggle-button />
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ __('messages.auth.min_chars') }}</p>
                <p x-show="fieldError('password')" x-cloak x-text="fieldError('password')" class="mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.confirm_password') }}</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                    <x-password-toggle-button />
                </div>
            </div>

            <button type="submit" :disabled="checking" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-wait disabled:opacity-60">
                <span x-show="!checking">{{ __('messages.auth.send_code_btn') }}</span>
                <span x-show="checking" x-cloak>…</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            {{ __('messages.auth.have_account') }}
            <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">{{ __('messages.nav.login') }}</a>
        </p>
    </div>

    <div x-show="step === 'otp'" x-cloak class="mt-6 space-y-5">
        <div>
            <label for="otp_code" class="block text-sm font-medium text-slate-700">{{ __('messages.auth.otp_code_label') }}</label>
            <input id="otp_code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                x-model="otpCode" @keyup.enter="verifyOtp()"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-center text-lg tracking-[0.5em] text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>

        <button type="button" @click="verifyOtp()" :disabled="verifying || otpCode.length !== 6"
            class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-wait disabled:opacity-60">
            <span x-show="!verifying">{{ __('messages.auth.verify_btn') }}</span>
            <span x-show="verifying" x-cloak>…</span>
        </button>

        <div class="flex items-center justify-between text-sm">
            <button type="button" @click="backToForm()" class="font-medium text-slate-500 hover:text-slate-700">
                {{ __('messages.auth.change_phone') }}
            </button>
            <button type="button" @click="resend()" :disabled="countdown > 0 || resending"
                class="font-semibold text-brand-700 hover:text-brand-800 disabled:cursor-not-allowed disabled:text-slate-400">
                <span x-show="countdown > 0" x-text="'{{ __('messages.auth.resend_btn') }} (' + countdown + 's)'"></span>
                <span x-show="countdown <= 0">{{ __('messages.auth.resend_btn') }}</span>
            </button>
        </div>
    </div>
</div>
@endsection
