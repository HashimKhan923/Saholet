@extends('layouts.provider')

@section('title', 'Coupons — ' . config('app.name'))
@section('page_title', 'Coupons')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
    <div>
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Coupons</h1>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Discount codes for your shop only — each customer can use a given code once.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- New coupon --}}
    <form method="POST" action="{{ route('provider.coupons.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          x-data="{ type: '{{ old('type', 'percentage') }}', submitting: false }" @submit="submitting = true">
        @csrf
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Create a coupon</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Code</label>
                <input type="text" name="code" required maxlength="32" value="{{ old('code') }}" placeholder="SAVE10"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Type</label>
                <select name="type" x-model="type" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="percentage">Percentage</option>
                    <option value="flat">Flat (Rs.)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Value <span x-text="type === 'percentage' ? '(%)' : '(Rs.)'"></span></label>
                <input type="number" name="value" required min="0.01" :max="type === 'percentage' ? 100 : 999999" step="0.01" value="{{ old('value') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Expires <span class="text-slate-400">(optional)</span></label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
        <button type="submit" :disabled="submitting" class="btn-shine mt-4 inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">Create coupon</span>
            <span x-show="submitting" x-cloak>Creating…</span>
        </button>
    </form>

    {{-- List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                <tr>
                    <th class="px-5 py-3">Code</th>
                    <th class="px-5 py-3">Discount</th>
                    <th class="px-5 py-3">Used</th>
                    <th class="px-5 py-3">Expires</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($coupons as $coupon)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3 font-mono font-semibold text-slate-900 dark:text-white">{{ $coupon->code }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $coupon->label() }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $coupon->redemptions_count }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $coupon->expires_at?->format('d M Y') ?? 'Never' }}</td>
                        <td class="px-5 py-3">
                            @if ($coupon->isUsable())
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $coupon->is_active ? 'Expired' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('provider.coupons.toggle-active', $coupon) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                        {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <x-confirm-form :action="route('provider.coupons.destroy', $coupon)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this coupon?" confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $coupons->links() }}</div>
</div>
@endsection
