@extends('layouts.app')

@section('title', 'Pay for booking — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.bookings.show', $booking) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Back to booking</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">How would you like to pay?</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Your job is complete. Choose how you'd like to settle payment.</p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->service->name }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }} · {{ $booking->reference }}</p>
            </div>
            <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $booking->price, 0) }}</p>
        </div>
    </div>

    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.payments.complete.store', $booking) }}" enctype="multipart/form-data" class="mt-6 space-y-5"
          x-data="{...fileDrop(), method: '{{ old('method', 'cash') }}', submitting: false}" @submit="submitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Payment method</h2>
            <div class="mt-4 space-y-3">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                       :class="method === 'cash' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="method" value="cash" x-model="method" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">Cash</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Pay the provider the full amount in cash, in person.</span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                       :class="method === 'bank_transfer' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                    <input type="radio" name="method" value="bank_transfer" x-model="method" class="mt-0.5 h-4 w-4 text-brand-600 focus:ring-brand-200">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">Bank transfer</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Transfer to Sahoulat's account and attach a screenshot as proof.</span>
                    </span>
                </label>
            </div>
        </div>

        <div x-show="method === 'bank_transfer'" x-cloak class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Send Rs. {{ number_format((float) $booking->price, 0) }} to</h2>
            <dl class="mt-4 space-y-2 text-sm">
                @if ($companyAccount['account_number'])
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Bank</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['bank_name'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Account title</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['account_title'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Account / IBAN</dt>
                        <dd class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['account_number'] }}</dd>
                    </div>
                @endif
                @if ($companyAccount['jazzcash_number'])
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">JazzCash</dt>
                        <dd class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['jazzcash_number'] }}</dd>
                    </div>
                @endif
                @if ($companyAccount['easypaisa_number'])
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Easypaisa</dt>
                        <dd class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $companyAccount['easypaisa_number'] }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-5">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Upload your payment screenshot</label>
                <div class="mt-2 flex items-center gap-2">
                    <x-file-drop name="screenshot" />
                </div>
                <x-field-error name="screenshot" />
                <p class="mt-1.5 text-xs text-slate-400">We'll confirm your transfer against our account and notify you once it's verified.</p>
            </div>
        </div>

        <button type="submit" :disabled="submitting"
            class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="! submitting" x-text="method === 'cash' ? 'Confirm cash payment' : 'Submit payment proof'"></span>
            <span x-show="submitting" x-cloak>Submitting…</span>
        </button>
    </form>
</section>
@endsection
