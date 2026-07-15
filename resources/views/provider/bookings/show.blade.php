@extends('layouts.provider')

@section('title', $booking->reference . ' — ' . config('app.name'))
@section('page_title', 'Booking detail')

@php $payment = $booking->activePayment(); @endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8" x-data="{ declining: false, cancelling: false }">

    {{-- ═══ Header ═══ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('provider.bookings.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Bookings
            </a>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $booking->service->name }}</h1>
                <x-booking-status :status="$booking->status" />
            </div>
            <p class="mt-1 font-mono text-sm text-slate-400">{{ $booking->reference }}</p>
        </div>

        @if (! $booking->isCancelled())
            <a href="{{ route('bookings.room', $booking) }}"
               class="btn-shine inline-flex shrink-0 items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-5A8 8 0 1 1 21 12z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Messages &amp; live tracking
            </a>
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        {{-- ═══ Left column ═══ --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Details --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Job details</h2>

                <dl class="mt-5 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                    @php
                        $rows = [
                            ['Customer', $booking->consumer->name, false],
                            ['Category', $booking->service->category->name, false],
                            ['Date', $booking->dateLabel(), false],
                            ['Time', $booking->timeLabel(), false],
                            ['Duration', '~ ' . $booking->duration_minutes . ' min', false],
                        ];
                    @endphp

                    @foreach ($rows as [$label, $value, $wide])
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                            <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $value }}</dd>
                        </div>
                    @endforeach

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Price</dt>
                        <dd class="mt-1 font-display text-lg font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $booking->price, 0) }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Service address</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $booking->address }}</dd>
                    </div>

                    @if ($booking->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Customer notes</dt>
                            <dd class="mt-1 rounded-xl bg-slate-50 px-4 py-3 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $booking->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Timeline --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Progress</h2>
                <div class="mt-6">
                    <x-booking-timeline :booking="$booking" />
                </div>
            </section>

            {{-- Review --}}
            @if ($booking->isCompleted() && $booking->review)
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Customer review</h2>
                        <time class="text-xs text-slate-400">{{ $booking->review->created_at->format('d M Y') }}</time>
                    </div>
                    <div class="mt-3">
                        <x-rating-stars :rating="$booking->review->rating" />
                    </div>
                    @if ($booking->review->comment)
                        <blockquote class="mt-3 rounded-xl border-s-4 border-brand-400 bg-slate-50 px-4 py-3 text-sm leading-relaxed text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $booking->review->comment }}
                        </blockquote>
                    @endif
                </section>
            @endif
        </div>

        {{-- ═══ Right column ═══ --}}
        <div class="space-y-6">

            {{-- Actions --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-24">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Actions</h2>

                @if ($booking->isPending())
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">This booking is waiting on you.</p>
                    <div class="mt-4 space-y-2.5">
                        <form method="POST" action="{{ route('provider.bookings.status', $booking) }}">
                            @csrf
                            <input type="hidden" name="action" value="confirm">
                            <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Confirm booking</button>
                        </form>
                        <button type="button" @click="declining = ! declining"
                            class="w-full rounded-xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">
                            Decline
                        </button>
                    </div>

                    <form x-show="declining" x-cloak x-collapse method="POST" action="{{ route('provider.bookings.status', $booking) }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="action" value="decline">
                        <label for="decline_reason" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Reason <span class="normal-case text-slate-400">(optional)</span></label>
                        <textarea id="decline_reason" name="cancellation_reason" rows="3"
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Submit decline</button>
                    </form>

                @elseif ($booking->isConfirmed())
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Start the job when you arrive on site.</p>
                    <div class="mt-4 space-y-2.5">
                        <form method="POST" action="{{ route('provider.bookings.status', $booking) }}">
                            @csrf
                            <input type="hidden" name="action" value="start">
                            <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Start job</button>
                        </form>
                        <button type="button" @click="cancelling = ! cancelling"
                            class="w-full rounded-xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">
                            Cancel booking
                        </button>
                    </div>

                    <form x-show="cancelling" x-cloak x-collapse method="POST" action="{{ route('provider.bookings.status', $booking) }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="action" value="cancel">
                        <label for="cancel_reason" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Reason <span class="normal-case text-slate-400">(optional)</span></label>
                        <textarea id="cancel_reason" name="cancellation_reason" rows="3"
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Submit cancellation</button>
                    </form>

                @elseif ($booking->isInProgress())
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mark complete once the work is finished.</p>
                    <form method="POST" action="{{ route('provider.bookings.status', $booking) }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="action" value="complete">
                        <button type="submit" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Mark completed</button>
                    </form>

                @else
                    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3.5 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        @if ($booking->isCompleted())
                            This job is completed. No further action needed.
                        @elseif ($booking->isCancelled())
                            This booking was cancelled @if ($booking->cancellation_reason) — {{ $booking->cancellation_reason }}@endif.
                        @endif
                    </div>
                @endif
            </section>

            {{-- Payment --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Payment</h2>
                    @if ($payment)<x-payment-status :status="$payment->status" />@endif
                </div>

                @if ($payment && $payment->isEscrow())
                    <p class="mt-3 font-display text-xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format((float) $payment->amount, 0) }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Held in escrow. It releases to your wallet once the customer confirms completion @if ($booking->hasOpenDispute()) <span class="font-semibold text-amber-600 dark:text-amber-400">(on hold — open dispute)</span>@endif.
                    </p>
                @elseif ($payment && $payment->isReleased())
                    <p class="mt-3 font-display text-xl font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $payment->amount, 0) }}</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Released to your <a href="{{ route('provider.wallet.index') }}" class="font-semibold text-brand-700 underline underline-offset-2 hover:text-brand-800 dark:text-brand-400">wallet</a>.
                    </p>
                @elseif ($payment && $payment->isRefunded())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">The escrow payment was refunded to the customer.</p>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No online payment yet — this may be settled in cash.</p>
                @endif
            </section>

            {{-- Dispute --}}
            @if ($booking->dispute || $booking->isDisputable())
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Dispute</h2>
                        @if ($booking->dispute)<x-dispute-status :status="$booking->dispute->status" />@endif
                    </div>

                    @if ($booking->dispute)
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                            <span class="font-mono text-xs text-slate-400">{{ $booking->dispute->reference }}</span><br>
                            {{ \Illuminate\Support\Str::limit($booking->dispute->reason, 120) }}
                        </p>
                        <a href="{{ route('disputes.show', $booking->dispute) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View dispute</a>
                    @else
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">A problem with this booking? Report it for our team to review.</p>
                        <a href="{{ route('bookings.dispute.create', $booking) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">Report a problem</a>
                    @endif
                </section>
            @endif
        </div>
    </div>
</div>
@endsection