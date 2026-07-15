@extends('layouts.provider')

@section('title', 'My services — ' . config('app.name'))
@section('page_title', 'My services')

@php
    $activeCount = $offered->where('is_active', true)->count();
    $pausedCount = $offered->count() - $activeCount;
    $avgPrice    = $offered->isNotEmpty() ? (float) $offered->avg('price') : 0.0;

    // A little variety across offering cards so the list doesn't read as one flat block.
    $accentCycle = ['from-brand-500 to-brand-700', 'from-sky-400 to-sky-600', 'from-violet-400 to-violet-600', 'from-amber-400 to-amber-600'];
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-900/[0.03] dark:border-slate-800 dark:bg-slate-900 sm:p-7">
        <div class="pointer-events-none absolute -end-16 -top-16 h-48 w-48 rounded-full bg-brand-500/[0.08] blur-3xl"></div>
        <div class="pointer-events-none absolute -start-10 bottom-0 h-32 w-32 rounded-full bg-sky-500/[0.06] blur-3xl"></div>

        <a href="{{ route('provider.dashboard') }}" class="relative inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dashboard
        </a>
        <div class="relative mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">My services</h1>
                <p class="mt-1.5 max-w-prose text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Only <span class="font-semibold text-slate-700 dark:text-slate-200">active</span> services appear in search and match you to jobs.
                </p>
            </div>
        </div>
    </div>

    @if (! $approved)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-md shadow-amber-900/10">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3z" stroke-linejoin="round"/><path d="M12 9v4M12 16.5v.2" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-amber-900 dark:text-amber-300">Verification required</h2>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">You need to be a verified provider before you can offer services.</p>
                    <a href="{{ route('provider.onboarding') }}" class="btn-shine mt-4 inline-flex items-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Go to verification</a>
                </div>
            </div>
        </div>
    @else
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- ═══ Summary ═══ --}}
        @if ($offered->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card label="Active" :value="$activeCount" hint="visible to customers" tone="brand">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12.5 5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </x-stat-card>
                <x-stat-card label="Paused" :value="$pausedCount" hint="hidden from search" tone="amber">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                </x-stat-card>
                <x-stat-card label="Average price" :value="$avgPrice" prefix="Rs. " hint="across your offerings" tone="violet">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M16 7H10a3 3 0 000 6h4a3 3 0 010 6H8" stroke-linecap="round"/></svg>
                </x-stat-card>
            </div>
        @endif

        {{-- ═══ Add a service ═══ --}}
        <section class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-900/[0.03] dark:border-slate-800 dark:bg-slate-900">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 via-sky-400 to-violet-500"></div>

            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                </span>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Add a service</h2>
            </div>

            @if ($available->isEmpty())
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">You've added every service available on the platform.</p>
            @else
                <form method="POST" action="{{ route('provider.services.store') }}" class="mt-4"
                    x-data="{
                        serviceId: '',
                        price: '',
                        basePrices: @js($available->mapWithKeys(fn ($s) => [$s->id => (int) $s->base_price])),
                        get basePrice() { return this.basePrices[this.serviceId] ?? null; },
                    }">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-[1fr_11rem_auto] sm:items-end">
                        <div>
                            <label for="service_id" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Service</label>
                            <select id="service_id" name="service_id" required x-model="serviceId"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">— Select a service —</option>
                                @foreach ($available->groupBy(fn ($s) => $s->category->name) as $categoryName => $services)
                                    <optgroup label="{{ $categoryName }}">
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="price" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Your price</label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-sm font-semibold text-slate-400">Rs.</span>
                                <input id="price" name="price" type="number" step="1" min="0" required inputmode="numeric" x-model="price"
                                    class="block w-full rounded-xl border border-slate-200 py-2.5 pe-3.5 ps-11 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            </div>
                        </div>

                        <button type="submit" class="btn-shine h-[42px] rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 px-6 text-sm font-semibold text-white shadow-sm shadow-brand-900/20 transition hover:shadow-md hover:shadow-brand-900/25">Add</button>
                    </div>

                    <template x-if="basePrice">
                        <p class="mt-3 text-xs text-slate-400">
                            Platform base price for this service is <span class="font-semibold text-slate-600 dark:text-slate-300">Rs. <span x-text="basePrice.toLocaleString()"></span></span>.
                            <button type="button" @click="price = basePrice" class="font-semibold text-brand-700 underline underline-offset-2 hover:text-brand-800 dark:text-brand-400">Use it</button>
                        </p>
                    </template>
                </form>
            @endif
        </section>

        {{-- ═══ Offerings ═══ --}}
        <section>
            <div class="mb-3 flex items-center justify-between px-1">
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Your offerings</h2>
                @if ($offered->isNotEmpty())
                    <span class="text-xs font-semibold text-slate-400">{{ $offered->count() }} {{ \Illuminate\Support\Str::plural('service', $offered->count()) }}</span>
                @endif
            </div>

            @forelse ($offered as $item)
                @php
                    $taken = (int) ($bookingCounts[$item->service_id] ?? 0);
                    $accent = $accentCycle[$loop->index % count($accentCycle)];
                @endphp
                <div class="card-lift group relative mb-3 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-900/[0.03] transition dark:border-slate-800 dark:bg-slate-900
                    {{ $item->is_active ? '' : 'opacity-70' }}"
                    x-data="{ price: {{ (int) $item->price }}, active: {{ $item->is_active ? 'true' : 'false' }}, get dirty() { return this.price !== {{ (int) $item->price }} || this.active !== {{ $item->is_active ? 'true' : 'false' }}; } }">

                    {{-- Left accent stripe --}}
                    <div class="absolute inset-y-0 start-0 w-1 bg-gradient-to-b {{ $item->is_active ? $accent : 'from-slate-300 to-slate-400 dark:from-slate-700 dark:to-slate-600' }}"></div>

                    <div class="flex flex-col gap-4 p-5 ps-6 lg:flex-row lg:items-center lg:justify-between">
                        {{-- Identity --}}
                        <div class="flex min-w-0 items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white shadow-md transition duration-300 group-hover:scale-105 group-hover:-rotate-3
                                {{ $item->is_active ? 'bg-gradient-to-br ' . $accent . ' shadow-slate-900/10' : 'bg-slate-300 dark:bg-slate-700 shadow-none' }}">
                                <x-service-icon :name="$item->service->category->slug ?? 'appliance'" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $item->service->name }}</p>
                                    @if ($item->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700 dark:bg-brand-950/50 dark:text-brand-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span> Live
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-700 dark:text-slate-400">Paused</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $item->service->category->name }}
                                    @if ($taken > 0)
                                        · <span class="font-semibold text-slate-500 dark:text-slate-400">{{ $taken }}</span> {{ \Illuminate\Support\Str::plural('booking', $taken) }} taken
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Controls --}}
                        <div class="flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('provider.services.update', $item) }}" class="flex flex-wrap items-center gap-3">
                                @csrf
                                @method('PUT')

                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-xs font-semibold text-slate-400">Rs.</span>
                                    <input name="price" type="number" step="1" min="0" required inputmode="numeric" x-model.number="price" aria-label="Price for {{ $item->service->name }}"
                                        class="w-32 rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-9 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-800">
                                </div>

                                {{-- Toggle switch --}}
                                <input type="hidden" name="is_active" :value="active ? 1 : 0">
                                <button type="button" role="switch" :aria-checked="active" @click="active = ! active"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent shadow-inner transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                    :class="active ? 'bg-gradient-to-r from-brand-500 to-brand-600' : 'bg-slate-200 dark:bg-slate-700'"
                                    aria-label="Toggle {{ $item->service->name }} active">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                                        :class="active ? 'translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                                </button>

                                <button type="submit" x-bind:disabled="! dirty"
                                    class="rounded-xl px-4 py-2 text-xs font-semibold shadow-sm transition disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
                                    :class="dirty ? 'bg-gradient-to-r from-brand-600 to-brand-700 text-white hover:shadow-md' : 'border border-slate-200 text-slate-500 dark:border-slate-700'">
                                    Save
                                </button>
                            </form>

                            <x-confirm-form :action="route('provider.services.destroy', $item)" method="DELETE"
                                button-label="Remove"
                                button-class="rounded-xl border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30"
                                title="Remove this service?" confirm-label="Remove" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-slate-700 dark:bg-slate-900">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400 dark:from-slate-800 dark:to-slate-800 dark:text-slate-600">
                        <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="3" width="16" height="18" rx="2"/><line x1="4" y1="9" x2="20" y2="9" stroke-linecap="round"/></svg>
                    </span>
                    <p class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">No services yet</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Add your first service above so customers can find and book you.</p>
                </div>
            @endforelse
        </section>
    @endif
</div>
@endsection
