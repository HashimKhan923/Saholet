@extends('layouts.admin')

@section('title', 'Review settlement — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ route('admin.settlements.index') }}" />
    </div>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $settlement->reference }}</h1>
        @php
            $statusTones = [
                'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                'confirmed' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
                'rejected' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
            ];
        @endphp
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusTones[$settlement->status] ?? '' }}">{{ ucfirst($settlement->status) }}</span>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Details</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $settlement->providerProfile->business_name ?: $settlement->providerProfile->user->name }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Method</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $settlement->methodLabel() }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Amount claimed</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $settlement->amount, 0) }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Current wallet balance</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $settlement->wallet->available_balance, 0) }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Submitted</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $settlement->created_at->format('d M Y, h:i A') }}</dd></div>
                    @if ($settlement->confirmed_at)
                        <div><dt class="text-slate-500 dark:text-slate-400">Processed</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $settlement->confirmed_at->format('d M Y, h:i A') }} by {{ $settlement->confirmedBy->name ?? '—' }}</dd></div>
                    @endif
                    @if ($settlement->confirmed_amount !== null)
                        <div><dt class="text-slate-500 dark:text-slate-400">Amount confirmed</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $settlement->confirmed_amount, 0) }}</dd></div>
                    @endif
                    @if ($settlement->admin_notes)
                        <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Notes</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $settlement->admin_notes }}</dd></div>
                    @endif
                </dl>
            </div>

            @if ($settlement->screenshot_path)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Transfer screenshot</h2>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settlement->screenshot_path) }}" target="_blank" rel="noopener" class="mt-4 block overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settlement->screenshot_path) }}" alt="Settlement screenshot" class="w-full">
                    </a>
                </div>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Decision</h2>

                @if ($settlement->isPending())
                    <form method="POST" action="{{ route('admin.settlements.confirm', $settlement) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="confirmed_amount" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Amount actually received (Rs.)</label>
                        <input id="confirmed_amount" name="confirmed_amount" type="number" step="1" min="1" max="{{ (int) $settlement->amount }}" required
                            value="{{ old('confirmed_amount', (int) $settlement->amount) }}"
                            class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <p class="text-xs text-slate-400">Can be less than the claimed amount if only a partial payment was received.</p>
                        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Confirm &amp; apply to wallet</button>
                    </form>

                    <form method="POST" action="{{ route('admin.settlements.reject', $settlement) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="admin_notes" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reason for rejection</label>
                        <textarea id="admin_notes" name="admin_notes" rows="3" required
                            class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('admin_notes') }}</textarea>
                        <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">Reject</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">This settlement has already been processed.</p>
                @endif
            </div>
        </aside>
    </div>
</section>
@endsection
