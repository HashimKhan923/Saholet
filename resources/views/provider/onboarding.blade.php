@extends('layouts.provider')

@section('title', 'Verification — ' . config('app.name'))
@section('page_title', 'Verification')

@php
    $inputBase = 'mt-1.5 block w-full rounded-xl border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-800 dark:text-white';
    $inputOk   = 'border-slate-200 focus:border-brand-400 focus:ring-brand-100 dark:border-slate-700 dark:focus:ring-brand-950';
    $inputBad  = 'border-red-400 focus:border-red-500 focus:ring-red-100';
    $maxMb     = (int) (config('kyc.max_size_kb') / 1024);
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

    {{-- ═══ Header ═══ --}}
    <div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
            <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dashboard
        </a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Provider verification</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Verified providers get listed in search, matched to jobs, and paid through escrow.</p>
    </div>

    {{-- ═══ Stepper ═══ --}}
    <x-onboarding-stepper :steps="$steps" :progress="$progress" />

    {{-- ═══ Status banners ═══ --}}
    @if ($profile->isRejected() && $profile->rejection_reason)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900/60 dark:bg-red-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-500 text-white">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M7 7l10 10M17 7 7 17" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-red-900 dark:text-red-300">Changes requested</p>
                    <p class="mt-1 text-sm text-red-800 dark:text-red-400/90">{{ $profile->rejection_reason }}</p>
                    <p class="mt-2 text-xs text-red-700/80 dark:text-red-400/70">Update what's flagged below and resubmit — there's no penalty for resubmitting.</p>
                </div>
            </div>
        </div>
    @elseif ($profile->isPending())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-amber-900 dark:text-amber-300">Application under review</p>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">
                        Submitted {{ $profile->submitted_at?->diffForHumans() ?? 'recently' }}. It can't be edited until our team makes a decision.
                    </p>
                </div>
            </div>
        </div>
    @elseif ($profile->isApproved())
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-900/60 dark:bg-brand-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-brand-900 dark:text-brand-300">Your account is verified</p>
                    <p class="mt-1 text-sm text-brand-800 dark:text-brand-400/90">
                        You're live. <a href="{{ route('provider.services.index') }}" class="font-semibold underline underline-offset-2">Manage your services</a> to start taking bookings.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    @if ($profile->isEditable())
        {{-- ═══════════ EDITABLE: details ═══════════ --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">1</span>
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Your details</h2>
            </div>

            <form method="POST" action="{{ route('provider.onboarding.update') }}" class="mt-6 space-y-5"
                x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                @method('PUT')

                <div>
                    <label for="business_name" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Business name <span class="normal-case text-slate-400">(optional)</span></label>
                    <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $profile->business_name) }}"
                        @error('business_name') aria-invalid="true" @enderror
                        class="{{ $inputBase }} @error('business_name') {{ $inputBad }} @else {{ $inputOk }} @enderror">
                    <x-field-error name="business_name" />
                </div>

                <div>
                    <label for="bio" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">About you / your work <span class="normal-case text-slate-400">(optional)</span></label>
                    <textarea id="bio" name="bio" rows="3" placeholder="What you specialise in, how long you've been doing it…"
                        @error('bio') aria-invalid="true" @enderror
                        class="{{ $inputBase }} placeholder:text-slate-400 @error('bio') {{ $inputBad }} @else {{ $inputOk }} @enderror">{{ old('bio', $profile->bio) }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-400">Shown on your public profile. Customers read this before booking.</p>
                    <x-field-error name="bio" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="experience_years" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Years of experience</label>
                        <input id="experience_years" name="experience_years" type="number" min="0" max="60" required
                            value="{{ old('experience_years', $profile->experience_years ?? 0) }}"
                            @error('experience_years') aria-invalid="true" @enderror
                            class="{{ $inputBase }} @error('experience_years') {{ $inputBad }} @else {{ $inputOk }} @enderror">
                        <x-field-error name="experience_years" />
                    </div>
                    <x-city-input :value="old('city', $profile->city)" :cities="$cities" />
                </div>

                <x-address-input name="address" label="Address" :value="old('address', $profile->address)" :required="false" />

                <div>
                    <label for="cnic_number" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">CNIC number</label>
                    <input id="cnic_number" name="cnic_number" type="text" required placeholder="42101-1234567-8"
                        value="{{ old('cnic_number', $profile->cnic_number) }}"
                        @error('cnic_number') aria-invalid="true" @enderror
                        class="{{ $inputBase }} font-mono placeholder:font-sans placeholder:text-slate-400 @error('cnic_number') {{ $inputBad }} @else {{ $inputOk }} @enderror">
                    <p class="mt-1.5 text-xs text-slate-400">Private. Used only to verify your identity — never shown to customers.</p>
                    <x-field-error name="cnic_number" />
                </div>

                <button type="submit" :disabled="submitting"
                    class="btn-shine inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-show="! submitting">Save details</span>
                    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Saving…
                    </span>
                </button>
            </form>
        </section>

        {{-- ═══════════ EDITABLE: documents ═══════════ --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">2</span>
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">KYC documents</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    {{ $uploadedRequired->count() }} / {{ $requiredTypes->count() }} required
                </span>
            </div>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">JPG, PNG or PDF — up to {{ $maxMb }} MB each. Documents are stored privately and only seen by our review team.</p>

            <div class="mt-6 space-y-3">
                @foreach ($documentTypes as $key => $meta)
                    @php
                        $doc = $profile->documentOfType($key);
                        $required = $meta['required'] ?? false;
                    @endphp

                    <div class="rounded-xl border p-4 transition
                        {{ $doc
                            ? 'border-brand-200 bg-brand-50/40 dark:border-brand-900/50 dark:bg-brand-950/20'
                            : ($required ? 'border-slate-200 dark:border-slate-700' : 'border-dashed border-slate-200 dark:border-slate-700') }}">

                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                    {{ $doc ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                                    @if ($doc)
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" stroke-linejoin="round"/><path d="M14 2v6h6" stroke-linejoin="round"/></svg>
                                    @endif
                                </span>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $meta['label'] }}</p>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide
                                            {{ $required
                                                ? 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400'
                                                : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                                            {{ $required ? 'Required' : 'Optional' }}
                                        </span>
                                    </div>
                                    @if ($doc)
                                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                            {{ $doc->original_name }} · {{ number_format($doc->size / 1024, 0) }} KB
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if ($doc)
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('provider-documents.show', $doc) }}" target="_blank" rel="noopener"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-white dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a>
                                    <x-confirm-form :action="route('provider.onboarding.documents.destroy', $doc)" method="DELETE"
                                        button-label="Remove"
                                        button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30"
                                        title="Remove this document?" confirm-label="Remove" />
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('provider.onboarding.documents.store') }}" enctype="multipart/form-data"
                            class="mt-3 flex items-center gap-2" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="type" value="{{ $key }}">
                            <x-file-drop name="file" />
                            <button type="submit" :disabled="submitting"
                                class="shrink-0 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="! submitting">{{ $doc ? 'Replace' : 'Upload' }}</span>
                                <span x-show="submitting" x-cloak>Uploading…</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ═══════════ EDITABLE: submit ═══════════ --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">3</span>
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Submit for review</h2>
            </div>

            @if (! empty($missing))
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Still needed before you can submit:</p>
                <ul class="mt-3 space-y-2">
                    @foreach ($missing as $item)
                        <li class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                            </span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-4 flex items-start gap-2.5 rounded-xl bg-brand-50 px-4 py-3 dark:bg-brand-950/30">
                    <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p class="text-sm text-brand-800 dark:text-brand-300">Everything's in place. Reviews usually take 1–2 business days.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('provider.onboarding.submit') }}" class="mt-5">
                @csrf
                <button type="submit" @disabled(! $canSubmit)
                    class="btn-shine w-full rounded-xl px-5 py-3 text-sm font-semibold shadow-sm transition sm:w-auto
                        {{ $canSubmit
                            ? 'bg-brand-600 text-white hover:bg-brand-700'
                            : 'cursor-not-allowed bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600' }}">
                    Submit application
                </button>
            </form>
        </section>

    @else
        {{-- ═══════════ READ-ONLY: pending / approved ═══════════ --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Your submission</h2>

            <dl class="mt-5 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                @php
                    $summary = [
                        'Business name' => $profile->business_name ?: '—',
                        'City'          => $profile->city ?: '—',
                        'Experience'    => $profile->experience_years . ' yr',
                        'CNIC'          => $profile->cnic_number ?: '—',
                    ];
                @endphp

                @foreach ($summary as $label => $value)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                        <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $value }}</dd>
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Address</dt>
                    <dd class="mt-1 font-medium text-slate-800 dark:text-slate-200">{{ $profile->address ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">About</dt>
                    <dd class="mt-1 leading-relaxed text-slate-700 dark:text-slate-300">{{ $profile->bio ?: '—' }}</dd>
                </div>
            </dl>

            <h3 class="mt-8 text-xs font-semibold uppercase tracking-wide text-slate-400">Documents</h3>
            <ul class="mt-3 space-y-2">
                @forelse ($profile->documents as $doc)
                    <li class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <span class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-white">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 12.5 10 17l9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $doc->label() }}</span>
                                <span class="block truncate text-xs text-slate-400">{{ $doc->original_name }}</span>
                            </span>
                        </span>
                        <a href="{{ route('provider-documents.show', $doc) }}" target="_blank" rel="noopener"
                           class="shrink-0 text-xs font-semibold text-brand-700 transition hover:text-brand-800 dark:text-brand-400">View</a>
                    </li>
                @empty
                    <li class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-400 dark:bg-slate-800">No documents on file.</li>
                @endforelse
            </ul>
        </section>
    @endif
</div>
@endsection