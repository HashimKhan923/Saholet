@extends('layouts.app')

@section('title', 'Pay for booking — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.bookings.show', $booking) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Back to booking</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Pay securely</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Your payment is held in escrow and only released to the provider after the job is completed and you confirm.</p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->service->name }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }} · {{ $booking->reference }}</p>
            </div>
            <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($booking->price, 0) }}</p>
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

    <form method="POST" action="{{ route('consumer.payments.store', $booking) }}" class="mt-6 space-y-5"
          x-data="{
              gateway: '{{ old('gateway', $gateways->first()?->key()) }}',
              applyCredit: {{ old('apply_credit') ? 'true' : 'false' }},
              price: {{ (float) $booking->price }},
              maxCredit: {{ (float) $maxCreditApplicable }},
              get remaining() { return Math.max(0, this.price - (this.applyCredit ? this.maxCredit : 0)); },
          }">
        @csrf

        @if ($maxCreditApplicable > 0)
            <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-900 dark:bg-brand-950/30">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="apply_credit" value="1" x-model="applyCredit" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                    <span class="text-sm">
                        <span class="block font-semibold text-slate-900 dark:text-white">Use my referral credit</span>
                        <span class="block text-xs text-slate-600 dark:text-slate-400">Apply Rs. {{ number_format($maxCreditApplicable, 0) }} credit to this payment.</span>
                    </span>
                </label>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-show="remaining > 0">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Choose a payment method</h2>
            <div class="mt-4 space-y-3">
                @foreach ($gateways as $g)
                    <label class="flex cursor-pointer items-center justify-between rounded-xl border p-4 transition"
                           :class="gateway === '{{ $g->key() }}' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 dark:bg-brand-950/40 dark:ring-brand-900' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'">
                        <span class="flex items-center gap-3">
                            <input type="radio" name="gateway" value="{{ $g->key() }}" x-model="gateway" class="h-4 w-4 text-brand-600 focus:ring-brand-200">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $g->label() }}</span>
                                @unless ($g->isAvailable())
                                    <span class="block text-xs text-amber-600 dark:text-amber-400">Not configured — choose Test payment to proceed locally.</span>
                                @endunless
                            </span>
                        </span>
                        @if ($g->key() === 'mock')
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Sandbox</span>
                        @endif
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <span x-show="remaining > 0" x-text="'Pay Rs. ' + remaining.toLocaleString()"></span>
            <span x-show="remaining <= 0" x-cloak>Pay with referral credit</span>
        </button>
        <p class="text-center text-xs text-slate-400 dark:text-slate-500">By paying you agree funds are held in escrow until you confirm completion.</p>
    </form>
</section>
@endsection