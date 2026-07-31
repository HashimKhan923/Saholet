@extends('layouts.provider')

@section('title', $booking->reference . ' — ' . config('app.name'))
@section('page_title', 'Booking detail')

@php $payment = $booking->activePayment(); @endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8" x-data="{ declining: false, cancelling: false }">

    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('provider.bookings.index') }}" />
    </div>

    {{-- ═══ Header ═══ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
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

            {{-- Completion proof --}}
            @if ($booking->isCompleted() && ($booking->completion_notes || $booking->completionPhotos->isNotEmpty()))
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Completion proof</h2>

                    @if ($booking->completion_notes)
                        <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $booking->completion_notes }}</p>
                    @endif

                    @if ($booking->beforePhotos->isNotEmpty())
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Before</p>
                        <div class="mt-2">
                            <x-photo-grid :photos="$booking->beforePhotos->map(fn ($p) => ['url' => $p->url()])->all()" />
                        </div>
                    @endif

                    @if ($booking->afterPhotos->isNotEmpty())
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">After</p>
                        <div class="mt-2">
                            <x-photo-grid :photos="$booking->afterPhotos->map(fn ($p) => ['url' => $p->url()])->all()" />
                        </div>
                    @endif
                </section>
            @endif

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
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Add before/after photos as proof of work, then mark complete.</p>
                    <form method="POST" action="{{ route('provider.bookings.status', $booking) }}" enctype="multipart/form-data" class="mt-4 space-y-4"
                          x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="action" value="complete">

                        {{-- Bold, hard-to-miss proof-of-work box — required to mark this booking complete --}}
                        <div class="rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 shadow-sm dark:border-amber-800 dark:bg-amber-950/30">
                            <div class="flex items-center gap-2">
                                <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 16.5v.5" stroke-linecap="round"/><path d="M10.3 3.3 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.3a2 2 0 0 0-3.4 0z" stroke-linejoin="round"/></svg>
                                <h3 class="font-display text-sm font-extrabold uppercase tracking-wide text-amber-900 dark:text-amber-300">Proof of work required</h3>
                            </div>
                            <p class="mt-1 text-xs text-amber-800 dark:text-amber-400">At least one before photo and one after photo are required to mark this job complete.</p>

                            <div class="mt-4">
                                <label class="block text-xs font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">Before photos <span class="font-semibold normal-case text-amber-700 dark:text-amber-500">(required, up to 6)</span></label>
                                <div class="mt-1.5"><x-photo-picker name="before_photos" :max="6" /></div>
                                <x-field-error name="before_photos" />
                            </div>
                            <div class="mt-4">
                                <label class="block text-xs font-bold uppercase tracking-wide text-amber-900 dark:text-amber-300">After photos <span class="font-semibold normal-case text-amber-700 dark:text-amber-500">(required, up to 6)</span></label>
                                <div class="mt-1.5"><x-photo-picker name="after_photos" :max="6" /></div>
                                <x-field-error name="after_photos" />
                            </div>
                        </div>

                        <div>
                            <label for="completion_notes" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Completion notes <span class="normal-case text-slate-400">(optional)</span></label>
                            <textarea id="completion_notes" name="completion_notes" rows="3"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        </div>
                        <button type="submit" :disabled="submitting" class="btn-shine w-full rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="!submitting">Mark completed</span>
                            <span x-show="submitting" x-cloak>Submitting…</span>
                        </button>
                    </form>

                    <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800" x-data="{ decliningAfterInspection: false }">
                        <button type="button" @click="decliningAfterInspection = ! decliningAfterInspection"
                            class="w-full rounded-xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">
                            Customer declined after inspection
                        </button>

                        <form x-show="decliningAfterInspection" x-cloak x-collapse method="POST" action="{{ route('provider.bookings.status', $booking) }}"
                              enctype="multipart/form-data" class="mt-4 space-y-3" x-data="{ visitMethod: 'cash', submitting: false }" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="action" value="cancel">

                            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/30">
                                <p class="text-xs font-semibold text-red-800 dark:text-red-400">
                                    The visit charge (Rs. {{ number_format((float) ($booking->service->visit_charge ?? 0), 0) }}) is yours to keep — it's not part of your platform commission.
                                </p>

                                <label class="mt-3 block text-xs font-semibold uppercase tracking-wide text-red-800 dark:text-red-400">How was the visit charge paid?</label>
                                <div class="mt-1.5 flex gap-2">
                                    <label class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-lg border p-2.5 text-sm font-semibold transition" :class="visitMethod === 'cash' ? 'border-red-500 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'">
                                        <input type="radio" name="visit_charge_method" value="cash" x-model="visitMethod" class="sr-only">
                                        Cash
                                    </label>
                                    <label class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-lg border p-2.5 text-sm font-semibold transition" :class="visitMethod === 'bank_transfer' ? 'border-red-500 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'">
                                        <input type="radio" name="visit_charge_method" value="bank_transfer" x-model="visitMethod" class="sr-only">
                                        Bank transfer
                                    </label>
                                </div>

                                <div x-show="visitMethod === 'bank_transfer'" x-cloak class="mt-3">
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-red-800 dark:text-red-400">Screenshot of the transfer</label>
                                    <input type="file" name="visit_charge_screenshot" accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
                                        class="mt-1.5 block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-red-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-red-700 hover:file:bg-red-200 dark:text-slate-300 dark:file:bg-red-950/50 dark:file:text-red-400">
                                    <x-field-error name="visit_charge_screenshot" />
                                </div>
                                <x-field-error name="visit_charge_method" />
                            </div>

                            <label for="decline_after_inspection_reason" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Notes <span class="normal-case text-slate-400">(optional)</span></label>
                            <textarea id="decline_after_inspection_reason" name="cancellation_reason" rows="2"
                                class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>

                            <button type="submit" :disabled="submitting" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="!submitting">Cancel booking — visit charge collected</span>
                                <span x-show="submitting" x-cloak>Submitting…</span>
                            </button>
                        </form>
                    </div>

                @else
                    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3.5 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        @if ($booking->isCompleted())
                            This job is completed. No further action needed.
                        @elseif ($booking->isCancelled())
                            This booking was cancelled @if ($booking->cancellation_reason) — {{ $booking->cancellation_reason }}@endif.
                            @if ($booking->hasVisitChargeCollected())
                                <p class="mt-2 font-semibold text-brand-700 dark:text-brand-400">
                                    Visit charge collected: Rs. {{ number_format((float) $booking->visit_charge_amount, 0) }} via {{ $booking->visit_charge_method === 'cash' ? 'cash' : 'bank transfer' }}.
                                </p>
                                @if ($booking->visitChargeScreenshotUrl())
                                    <a href="{{ $booking->visitChargeScreenshotUrl() }}" target="_blank" rel="noopener" class="mt-1 inline-block text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">View transfer screenshot &rarr;</a>
                                @endif
                            @endif
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
                        Pending. It releases to your wallet once the customer confirms completion @if ($booking->hasOpenDispute()) <span class="font-semibold text-amber-600 dark:text-amber-400">(on hold — open dispute)</span>@endif.
                    </p>
                @elseif ($payment && $payment->isReleased())
                    <p class="mt-3 font-display text-xl font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format((float) $payment->amount, 0) }}</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Released to your <a href="{{ route('provider.wallet.index') }}" class="font-semibold text-brand-700 underline underline-offset-2 hover:text-brand-800 dark:text-brand-400">wallet</a>.
                    </p>
                @elseif ($payment && $payment->isRefunded())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">The pending payment was refunded to the customer.</p>
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