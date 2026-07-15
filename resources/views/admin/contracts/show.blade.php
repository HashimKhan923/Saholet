@extends('layouts.admin')

@section('title', $contract->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.contracts.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Contracts</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $contract->title }}</h1>
        <x-contract-status :status="$contract->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $contract->reference }} · {{ $contract->consumer->name }} ({{ $contract->consumer->email }})</p>

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

    {{-- Project details --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->consumer->phone ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->city }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Preferred start</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->preferred_start_date?->format('D, d M Y') ?? 'Flexible' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Site address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->address }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Description</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $contract->description }}</dd></div>
        </dl>

        <x-photo-gallery :photos="$contract->photos" />
    </div>

    {{-- Quoting form --}}
    @if ($contract->isSubmitted())
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
             x-data="{
                 milestones: [{ title: 'Deposit', amount: '' }],
                 addMilestone() { this.milestones.push({ title: '', amount: '' }) },
                 removeMilestone(i) { if (this.milestones.length > 1) this.milestones.splice(i, 1) },
             }">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Send a quote</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Price each service and define the payment schedule.</p>

            <form method="POST" action="{{ route('admin.contracts.quote', $contract) }}" class="mt-6 space-y-6">
                @csrf

                <div class="space-y-3">
                    @foreach ($contract->items as $item)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->service->name }} <span class="font-normal text-slate-400 dark:text-slate-500">&times;{{ $item->quantity }}</span></p>
                                @if ($item->notes)
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $item->notes }}</p>
                                @endif
                            </div>
                            <div class="w-40">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Price (Rs.)</label>
                                <input type="number" step="1" min="0" name="items[{{ $item->id }}][price]" required
                                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <label for="admin_notes" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Notes for the consumer <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                    <textarea id="admin_notes" name="admin_notes" rows="3"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Payment schedule</h3>
                    <div class="mt-3 space-y-3">
                        <template x-for="(milestone, index) in milestones" :key="index">
                            <div class="flex items-end gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Title</label>
                                    <input type="text" :name="'milestones[' + index + '][title]'" x-model="milestone.title" required placeholder="e.g. Deposit, Progress, Final"
                                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                </div>
                                <div class="w-40">
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Amount (Rs.)</label>
                                    <input type="number" step="1" min="0" :name="'milestones[' + index + '][amount]'" x-model="milestone.amount" required
                                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                </div>
                                <button type="button" x-show="milestones.length > 1" @click="removeMilestone(index)" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-slate-700 dark:text-red-400 dark:hover:bg-red-950/40">Remove</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addMilestone()" class="mt-3 inline-flex items-center rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:text-slate-400 dark:hover:border-brand-700 dark:hover:text-brand-400">+ Add milestone</button>
                </div>

                <button type="submit" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Send quote</button>
            </form>
        </div>
    @endif

    {{-- Items + assignment --}}
    <div class="mt-8">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Services</h2>
        <div class="mt-4 space-y-3">
            @foreach ($contract->items as $item)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->service->name }} <span class="font-normal text-slate-400 dark:text-slate-500">&times;{{ $item->quantity }}</span></p>
                            @if ($item->quoted_price)
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Quoted Rs. {{ number_format($item->quoted_price, 0) }}</p>
                            @endif
                            @if ($item->providerProfile)
                                <p class="mt-2 text-xs font-medium text-brand-700 dark:text-brand-400">
                                    Assigned to {{ $item->providerProfile->business_name ?: $item->providerProfile->user->name }}
                                    @if ($item->booking)
                                        &middot; Booking {{ $item->booking->reference }} ({{ ucfirst(str_replace('_', ' ', $item->booking->status)) }})
                                    @endif
                                </p>
                            @endif
                        </div>
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span>
                    </div>

                    @if ($item->isAssignable())
                        @php $providers = $eligibleProviders[$item->id] ?? collect(); @endphp
                        <form method="POST" action="{{ route('admin.contracts.items.assign', [$contract, $item]) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4 dark:border-slate-800">
                            @csrf
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Provider</label>
                                <select name="provider_profile_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    <option value="">— Select —</option>
                                    @forelse ($providers as $provider)
                                        <option value="{{ $provider->id }}">{{ $provider->business_name ?: $provider->user->name }} ({{ $provider->city ?: 'n/a' }})</option>
                                    @empty
                                        <option value="" disabled>No approved providers offer this service</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Date</label>
                                <input type="date" name="scheduled_date" required min="{{ now()->toDateString() }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Time</label>
                                <input type="time" name="scheduled_time" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div class="sm:col-span-4">
                                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Assign provider</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Milestones --}}
    @if ($contract->milestones->isNotEmpty())
        <div class="mt-8">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Payment schedule</h2>
            <div class="mt-4 space-y-3">
                @foreach ($contract->milestones as $milestone)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $milestone->title }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="font-display text-base font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($milestone->amount, 0) }}</p>
                            <x-payment-status :status="$milestone->status" />
                            @if ($milestone->isEscrow())
                                <x-confirm-form :action="route('admin.contracts.milestones.release', [$contract, $milestone])"
                                    button-label="Release" button-class="rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700"
                                    title="Release this milestone?" message="Funds become available for this contract's costs." confirm-label="Release" confirm-class="bg-brand-600 hover:bg-brand-700" />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
