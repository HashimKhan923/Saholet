@extends('layouts.app')

@section('title', 'Report a problem — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ $backUrl }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to booking</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900">Report a problem</h1>
    <p class="mt-2 text-sm text-slate-600">
        {{ $booking->service->name }} · {{ $booking->reference }}. Our team will review and resolve it.
        @if ($booking->activePayment() && $booking->activePayment()->isEscrow())
            Any escrow payment is held until this is resolved.
        @endif
    </p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bookings.dispute.store', $booking) }}" class="mt-6 space-y-6">
        @csrf
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <label for="reason" class="block text-sm font-medium text-slate-700">What went wrong?</label>
            <textarea id="reason" name="reason" rows="5" required maxlength="2000" placeholder="Describe the issue in as much detail as possible…"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">{{ old('reason') }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Submit dispute</button>
            <a href="{{ $backUrl }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</section>
@endsection