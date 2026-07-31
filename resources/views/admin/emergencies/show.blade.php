@extends('layouts.admin')

@section('title', $emergencyRequest->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('admin.emergencies.index') }}" />
    </div>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $emergencyRequest->reference }}</h1>
        <x-emergency-status :status="$emergencyRequest->status" />
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Service</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->service->name }} ({{ $emergencyRequest->service->category->name }})</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Customer</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->consumer->name }} · {{ $emergencyRequest->consumer->phone }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->city }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Submitted</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->created_at->format('d M Y, g:i A') }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->address }}</dd></div>
            @if ($emergencyRequest->notes)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Customer notes</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $emergencyRequest->notes }}</dd></div>
            @endif
        </dl>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    @if ($emergencyRequest->isOpen())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Send a price quote</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The customer will see this price and can accept or decline it.</p>
            <form method="POST" action="{{ route('admin.emergencies.quote', $emergencyRequest) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Price (Rs.)</label>
                    <input type="number" name="quoted_price" min="0" step="0.01" required value="{{ old('quoted_price') }}"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Note to customer <span class="text-slate-400">(optional)</span></label>
                    <textarea name="admin_note" rows="3" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('admin_note') }}</textarea>
                </div>
                <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Send quote</button>
            </form>
        </div>
    @elseif ($emergencyRequest->isQuoted())
        <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-900/60 dark:bg-sky-950/30">
            <p class="text-sm text-sky-800 dark:text-sky-400/90">Quoted <strong>Rs. {{ number_format((float) $emergencyRequest->quoted_price, 0) }}</strong> by {{ $emergencyRequest->quotedBy?->name }} on {{ $emergencyRequest->quoted_at->format('d M Y, g:i A') }}. Waiting for the customer to respond.</p>
        </div>
    @elseif ($emergencyRequest->isAccepted())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Assign a provider</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Customer accepted Rs. {{ number_format((float) $emergencyRequest->quoted_price, 0) }}. Choose an approved provider who offers this service in {{ $emergencyRequest->city }}.</p>

            @if ($candidateProviders->isEmpty())
                <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                    No approved providers currently offer this service in {{ $emergencyRequest->city }}.
                </div>
            @else
                <form method="POST" action="{{ route('admin.emergencies.assign', $emergencyRequest) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                    @csrf
                    <select name="provider_profile_id" required class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        @foreach ($candidateProviders as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->business_name ?: $provider->user->name }} — {{ number_format((float) $provider->rating_avg, 1) }}★ ({{ $provider->reviews_count }} reviews)</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Assign & confirm</button>
                </form>
            @endif
        </div>
    @elseif ($emergencyRequest->isMatched())
        <div class="mt-6 rounded-2xl border border-brand-200 bg-brand-50 p-6 dark:border-brand-900/60 dark:bg-brand-950/30">
            <p class="text-sm text-brand-800 dark:text-brand-400/90">Assigned to {{ $emergencyRequest->matchedProvider?->business_name ?: $emergencyRequest->matchedProvider?->user->name }}.</p>
            @if ($emergencyRequest->booking)
                <a href="{{ route('admin.bookings.show', $emergencyRequest->booking) }}" class="mt-3 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">View booking</a>
            @endif
        </div>
    @elseif ($emergencyRequest->isDeclined())
        <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400">The customer declined this quote.</div>
    @elseif ($emergencyRequest->isCancelled())
        <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400">This request was cancelled by the customer.</div>
    @endif
</section>
@endsection
