@extends('layouts.admin')

@section('title', 'New booking — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.bookings.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Bookings</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Create a manual booking</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">For customers booked over the phone or in person, without the app.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bookings.store') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        x-data="{
            providerServiceMap: @js($providerServiceMap),
            providerId: '{{ old('provider_profile_id') }}',
            serviceId: '{{ old('service_id') }}',
            price: '{{ old('price') }}',
            submitting: false,
            get services() { return this.providerServiceMap[this.providerId] || [] },
            selectService(id) {
                this.serviceId = id;
                const svc = this.services.find(s => String(s.service_id) === String(id));
                if (svc) this.price = svc.price;
            },
        }"
        @submit="submitting = true">
        @csrf

        <div>
            <label for="consumer_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Customer</label>
            <select id="consumer_id" name="consumer_id" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">— Select —</option>
                @foreach ($consumers as $consumer)
                    <option value="{{ $consumer->id }}" @selected(old('consumer_id') == $consumer->id)>{{ $consumer->name }} — {{ $consumer->phone ?: $consumer->email }}</option>
                @endforeach
            </select>
            <x-field-error name="consumer_id" />
        </div>

        <div>
            <label for="provider_profile_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Provider</label>
            <select id="provider_profile_id" name="provider_profile_id" x-model="providerId" @change="serviceId = ''; price = ''" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">— Select —</option>
                @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}" @selected(old('provider_profile_id') == $provider->id)>{{ $provider->business_name ?: $provider->user->name }} — {{ $provider->city }}</option>
                @endforeach
            </select>
            <x-field-error name="provider_profile_id" />
        </div>

        <div>
            <label for="service_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Service</label>
            <select id="service_id" name="service_id" x-model="serviceId" @change="selectService($event.target.value)" required :disabled="!providerId"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">— Select a provider first —</option>
                <template x-for="s in services" :key="s.service_id">
                    <option :value="s.service_id" x-text="s.name + ' — Rs. ' + s.price"></option>
                </template>
            </select>
            <x-field-error name="service_id" />
        </div>

        <div>
            <label for="price" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Price (Rs.)</label>
            <input id="price" type="number" name="price" x-model="price" min="0" step="0.01" required
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Auto-filled from the provider's listed price — override if needed.</p>
            <x-field-error name="price" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="scheduled_date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Date</label>
                <input id="scheduled_date" type="date" name="scheduled_date" required value="{{ old('scheduled_date', now()->toDateString()) }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <x-field-error name="scheduled_date" />
            </div>
            <div>
                <label for="scheduled_time" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Time</label>
                <input id="scheduled_time" type="time" name="scheduled_time" required value="{{ old('scheduled_time') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <x-field-error name="scheduled_time" />
            </div>
        </div>

        <div>
            <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Service address</label>
            <input id="address" type="text" name="address" required value="{{ old('address') }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="address" />
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
            <select id="status" name="status" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>Confirmed</option>
                <option value="pending" @selected(old('status') === 'pending')>Pending (needs provider confirmation)</option>
            </select>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Notes <span class="text-slate-400">(optional)</span></label>
            <textarea id="notes" name="notes" rows="3" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" :disabled="submitting" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Create booking</span>
                <span x-show="submitting" x-cloak>Creating…</span>
            </button>
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Cancel</a>
        </div>
    </form>
</section>
@endsection
