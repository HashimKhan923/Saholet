@extends('layouts.admin')

@section('title', $booking->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.bookings.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Bookings</a>

    <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $booking->service->name ?? 'Booking' }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $booking->reference }} · Booked {{ $booking->created_at->format('d M Y, g:i A') }}</p>
        </div>
        <x-booking-status :status="$booking->status" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Parties --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Consumer</p>
                    <p class="mt-1.5 font-medium text-slate-900 dark:text-white">{{ $booking->consumer->name ?? '—' }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $booking->consumer->email ?? '' }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $booking->consumer->phone ?? '' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Provider</p>
                    @if ($booking->providerProfile)
                        <p class="mt-1.5 font-medium text-slate-900 dark:text-white">{{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $booking->providerProfile->user->email ?? '' }}</p>
                        <a href="{{ route('admin.providers.show', $booking->providerProfile) }}" class="mt-1 inline-block text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">View provider profile &rarr;</a>
                    @else
                        <p class="mt-1.5 text-sm text-slate-400">—</p>
                    @endif
                </div>
            </div>

            {{-- Job details --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Details</p>
                <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-400">Category</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $booking->service->category->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Duration</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $booking->duration_minutes }} min</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Scheduled</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-300">{{ \Illuminate\Support\Carbon::parse($booking->scheduled_date)->format('d M Y') }} at {{ substr($booking->scheduled_time, 0, 5) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Price</dt>
                        <dd class="text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format((float) $booking->price, 0) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-400">Address</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $booking->address }}</dd>
                    </div>
                    @if ($booking->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-slate-400">Notes</dt>
                            <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $booking->notes }}</dd>
                        </div>
                    @endif
                    @if ($booking->status === 'cancelled')
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-slate-400">Cancelled by {{ $booking->cancelled_by }}</dt>
                            <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $booking->cancellation_reason ?: '—' }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Timeline --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Timeline</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    <li>Booked — {{ $booking->created_at->format('d M Y, g:i A') }}</li>
                    @if ($booking->confirmed_at)<li>Confirmed — {{ $booking->confirmed_at->format('d M Y, g:i A') }}</li>@endif
                    @if ($booking->started_at)<li>Started — {{ $booking->started_at->format('d M Y, g:i A') }}</li>@endif
                    @if ($booking->completed_at)<li>Completed — {{ $booking->completed_at->format('d M Y, g:i A') }}</li>@endif
                    @if ($booking->cancelled_at)<li>Cancelled — {{ $booking->cancelled_at->format('d M Y, g:i A') }}</li>@endif
                </ul>
            </div>

            @if ($booking->review)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Review</p>
                    <div class="mt-2 flex items-center gap-1 text-amber-400">
                        @for ($s = 0; $s < 5; $s++)
                            <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $s < $booking->review->rating ? '' : 'text-slate-200 dark:text-slate-700' }}" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9 5.9 20.4l1.5-6.8L2.2 9l6.9-.7L12 2z"/></svg>
                        @endfor
                    </div>
                    @if ($booking->review->comment)
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">"{{ $booking->review->comment }}"</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Payments --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Payments</p>
                <div class="mt-3 space-y-3">
                    @forelse ($booking->payments as $payment)
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3 text-sm last:border-0 last:pb-0 dark:border-slate-800">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">Rs. {{ number_format((float) $payment->amount, 0) }}</p>
                                <p class="text-xs text-slate-400">{{ strtoupper($payment->gateway) }} · {{ $payment->reference }}</p>
                            </div>
                            <x-payment-status :status="$payment->status" />
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No payment recorded yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Dispute --}}
            @if ($booking->dispute)
                <div class="rounded-2xl border border-red-200 bg-red-50/40 p-5 dark:border-red-900/40 dark:bg-red-950/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-500">Dispute</p>
                    <p class="mt-1.5 text-sm font-medium text-slate-900 dark:text-white">{{ $booking->dispute->reference }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $booking->dispute->status }}</p>
                    <a href="{{ route('admin.disputes.show', $booking->dispute) }}" class="mt-3 inline-block text-xs font-semibold text-red-700 hover:text-red-800 dark:text-red-400">Review dispute &rarr;</a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
