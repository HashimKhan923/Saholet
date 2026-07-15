@extends('layouts.app')

@section('title', 'Book ' . $service->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('services.show', $service) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Back to {{ $service->name }}</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Book this service</h1>

    {{-- Summary --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $service->name }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    {{ $provider->business_name ?: $provider->user->name }} · {{ $provider->city ?: 'Pakistan' }}
                </p>
            </div>
            <div class="text-right">
                <p class="font-display text-lg font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($providerService->price, 0) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">~ {{ $service->duration_minutes }} min</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    {{-- Date picker (GET reload) --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">1. Choose a date</h2>
        <form method="GET" action="{{ route('consumer.bookings.create', ['provider' => $provider->id, 'service' => $service->slug]) }}" class="mt-3">
            <div class="flex flex-wrap items-center gap-3">
                <select name="date" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    @foreach ($dates as $d)
                        <option value="{{ $d['value'] }}" @selected($selectedDate === $d['value'])>{{ $d['label'] }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Show times</button></noscript>
            </div>
        </form>
    </div>

    {{-- Booking form (POST) --}}
    <form method="POST" action="{{ route('consumer.bookings.store', ['provider' => $provider->id, 'service' => $service->slug]) }}"
        class="mt-6 space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <input type="hidden" name="scheduled_date" value="{{ $selectedDate }}">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">2. Pick a time slot</h2>

            @php $hasAvailable = collect($slots)->contains('available', true); @endphp

            @if ($hasAvailable)
                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                    @foreach ($slots as $slot)
                        @if ($slot['available'])
                            <label class="cursor-pointer">
                                <input type="radio" name="scheduled_time" value="{{ $slot['value'] }}" class="peer sr-only"
                                    @checked(old('scheduled_time') === $slot['value']) required>
                                <span class="block rounded-lg border border-slate-200 px-3 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:border-brand-300 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand-700 dark:peer-checked:border-brand-500 dark:peer-checked:bg-brand-950/40 dark:peer-checked:text-brand-400">
                                    {{ $slot['label'] }}
                                </span>
                            </label>
                        @else
                            <span class="block cursor-not-allowed rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 text-center text-sm font-medium text-slate-300 line-through dark:border-slate-800 dark:bg-slate-800 dark:text-slate-600">
                                {{ $slot['label'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                    No available times for this date. Please choose another date above.
                </p>
            @endif
            <x-field-error name="scheduled_time" />
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">3. Service address &amp; details</h2>

            <div class="mt-4 space-y-4">
                <x-address-input :value="old('address')" />
                <div>
                    <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Notes <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Anything the provider should know"
                        @error('notes') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                            @error('notes') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-800 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('notes') }}</textarea>
                    <x-field-error name="notes" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="submitting || {{ $hasAvailable ? 'false' : 'true' }}"
                class="inline-flex items-center rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Confirm booking</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Booking…
                </span>
            </button>
            <a href="{{ route('services.show', $service) }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        </div>

        <p class="text-xs text-slate-400 dark:text-slate-500">No payment is taken now — you’ll settle with the provider per the service terms.</p>
    </form>
</section>
@endsection