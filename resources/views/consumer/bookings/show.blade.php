@extends('layouts.app')

@section('title', $booking->reference . ' — ' . config('app.name'))

@section('content')
@php $payment = $booking->activePayment(); @endphp
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.bookings.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; My bookings</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $booking->service->name }}</h1>
        <x-booking-status :status="$booking->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Reference {{ $booking->reference }}</p>

    @if (! $booking->isCancelled())
        <a href="{{ route('bookings.room', $booking) }}"
           class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-5A8 8 0 1 1 21 12z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Messages &amp; live tracking
        </a>
    @endif

    @if ($booking->isCancelled() && $booking->cancellation_reason)
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <span class="font-semibold">Cancelled:</span> {{ $booking->cancellation_reason }}
        </div>
    @endif

    {{-- Payment panel --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Payment</h2>
            <div class="flex items-center gap-3">
                @if ($payment && in_array($payment->status, ['escrow', 'released'], true))
                    <a href="{{ route('bookings.receipt', $booking) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Receipt
                    </a>
                @endif
                @if ($payment)<x-payment-status :status="$payment->status" />@endif
            </div>
        </div>

        @if ($payment && $payment->isReleased())
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">You released <span class="font-semibold">Rs. {{ number_format($payment->amount, 0) }}</span> to the provider. Thank you!</p>
        @elseif ($payment && $payment->isRefunded())
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Your escrow payment of Rs. {{ number_format($payment->amount, 0) }} was refunded.</p>
        @elseif ($payment && $payment->isEscrow())
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                <span class="font-semibold">Rs. {{ number_format($payment->amount, 0) }}</span> is held safely in escrow.
                @if ($booking->isCompleted())
                    The provider has marked the job complete — please confirm to release payment.
                @else
                    It releases to the provider after the job is completed and you confirm.
                @endif
            </p>

            @if ($booking->isCompleted() && config('payments.release_mode') === 'consumer_confirm')
                @if ($booking->hasOpenDispute())
                    <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-400">Resolve the open dispute before releasing payment.</p>
                @else
                    <div class="mt-4">
                        <x-confirm-form :action="route('consumer.payments.release', $booking)"
                            button-label="Confirm & release payment" button-class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                            title="Release payment to the provider?" confirm-label="Release payment" confirm-class="bg-brand-600 hover:bg-brand-700" />
                    </div>
                @endif
            @endif
        @elseif ($booking->isPayable())
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Pay now to hold the amount in escrow until the job is complete. Or pay the provider directly (cash) — your choice.</p>
            <a href="{{ route('consumer.payments.create', $booking) }}" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Pay Rs. {{ number_format($booking->price, 0) }}</a>
        @else
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No online payment on this booking.</p>
        @endif
    </div>

    {{-- Review panel --}}
    @if ($booking->isCompleted())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Your review</h2>
            @if ($booking->review)
                <div class="mt-3">
                    <x-rating-stars :rating="$booking->review->rating" />
                    @if ($booking->review->comment)
                        <p class="mt-2 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $booking->review->comment }}</p>
                    @endif
                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Posted {{ $booking->review->created_at->format('d M Y') }}</p>
                </div>
            @else
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">How did it go? Your feedback helps other customers.</p>
                <a href="{{ route('consumer.reviews.create', $booking) }}" class="mt-3 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Leave a review</a>
            @endif
        </div>
    @endif

    {{-- Dispute panel --}}
    @if ($booking->dispute || $booking->isDisputable())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Dispute</h2>
                @if ($booking->dispute)<x-dispute-status :status="$booking->dispute->status" />@endif
            </div>
            @if ($booking->dispute)
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ $booking->dispute->reference }} — {{ \Illuminate\Support\Str::limit($booking->dispute->reason, 120) }}</p>
                <a href="{{ route('disputes.show', $booking->dispute) }}" class="mt-3 inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View dispute</a>
            @else
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Something not right with this booking? Report it and our team will help.</p>
                <a href="{{ route('bookings.dispute.create', $booking) }}" class="mt-3 inline-flex items-center rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/40">Report a problem</a>
            @endif
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->service->category->name }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Date</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->dateLabel() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Time</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->timeLabel() }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Duration</dt><dd class="font-medium text-slate-800 dark:text-slate-200">~ {{ $booking->duration_minutes }} min</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Price</dt><dd class="font-semibold text-brand-700 dark:text-brand-400">Rs. {{ number_format($booking->price, 0) }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Service address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->address }}</dd></div>
            @if ($booking->notes)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $booking->notes }}</dd></div>
            @endif
        </dl>
    </div>

    @if ($booking->canBeCancelledByConsumer())
        <div class="mt-6">
            <x-confirm-form :action="route('consumer.bookings.cancel', $booking)"
                button-label="Cancel booking" button-class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/40"
                title="Cancel this booking?"
                :message="$payment && $payment->isEscrow() ? 'Your escrow payment will be refunded.' : ''"
                confirm-label="Cancel booking" />
        </div>
    @endif
</section>
@endsection