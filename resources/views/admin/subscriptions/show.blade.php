@extends('layouts.admin')

@section('title', $subscription->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Subscriptions</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $subscription->plan->name }}</h1>
        <x-subscription-status :status="$subscription->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subscription->reference }} · {{ $subscription->consumer->name }} ({{ $subscription->consumer->email }})</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->consumer->phone ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->city }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Next visit</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->next_visit_date->format('D, d M Y') }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Visits used</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->visits_used }}{{ $subscription->plan->total_visits ? ' / ' . $subscription->plan->total_visits : ' (ongoing)' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Service address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->address }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Price per visit</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format($subscription->plan->price_per_visit, 0) }}</dd></div>
            @if ($subscription->providerProfile)
                <div><dt class="text-slate-500 dark:text-slate-400">Assigned provider</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $subscription->providerProfile->business_name ?: $subscription->providerProfile->user->name }}</dd></div>
            @endif
        </dl>
    </div>

    @if ($subscription->isPendingAssignment())
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Assign a provider</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">This provider will be reused automatically for every future visit on this subscription.</p>

            <form method="POST" action="{{ route('admin.subscriptions.assign', $subscription) }}" class="mt-4 grid gap-3 sm:grid-cols-3">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Provider</label>
                    <select name="provider_profile_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="">— Select —</option>
                        @forelse ($eligibleProviders as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->business_name ?: $provider->user->name }} ({{ $provider->city ?: 'n/a' }})</option>
                        @empty
                            <option value="" disabled>No approved providers offer this service</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">First visit time</label>
                    <input type="time" name="scheduled_time" required value="10:00" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Assign & schedule first visit</button>
                </div>
            </form>
        </div>
    @endif

    @if ($subscription->bookings->isNotEmpty())
        <div class="mt-8">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Visit history</h2>
            <div class="mt-4 space-y-3">
                @foreach ($subscription->bookings as $booking)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->reference }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->scheduled_date->format('d M Y') }}</p>
                        </div>
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
