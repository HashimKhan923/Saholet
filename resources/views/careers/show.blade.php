@extends('layouts.app')

@section('title', $listing->title . ' — ' . config('app.name'))

@push('jsonld')
@php
    $employmentTypeMap = [
        'full_time' => 'FULL_TIME',
        'part_time' => 'PART_TIME',
        'contract' => 'CONTRACTOR',
        'internship' => 'INTERN',
    ];

    $jobSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $listing->title,
        'description' => nl2br(e($listing->description)),
        'datePosted' => $listing->created_at->toDateString(),
        'employmentType' => $employmentTypeMap[$listing->employment_type] ?? 'OTHER',
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'sameAs' => url('/'),
        ],
        'jobLocationType' => $listing->is_remote ? 'TELECOMMUTE' : null,
        'jobLocation' => $listing->city ? [
            '@type' => 'Place',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => $listing->city, 'addressCountry' => 'PK'],
        ] : null,
        'validThrough' => $listing->closes_at?->toIso8601String(),
    ];

    if ($listing->salary_min || $listing->salary_max) {
        $jobSchema['baseSalary'] = [
            '@type' => 'MonetaryAmount',
            'currency' => 'PKR',
            'value' => [
                '@type' => 'QuantitativeValue',
                'minValue' => (string) ($listing->salary_min ?? $listing->salary_max),
                'maxValue' => (string) ($listing->salary_max ?? $listing->salary_min),
                'unitText' => 'MONTH',
            ],
        ];
    }

    $jobSchema = array_filter($jobSchema, fn ($v) => $v !== null);
@endphp
<script type="application/ld+json">{!! json_encode($jobSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('careers.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; {{ __('messages.careers.back_link') }}</a>

    <span class="mt-4 inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $listing->category->name }}</span>
    <h1 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">{{ $listing->title }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        {{ ucfirst(str_replace('_', ' ', $listing->employment_type)) }}
        @if ($listing->is_remote) &middot; {{ __('messages.careers.remote_label') }} @endif
        @if ($listing->city) &middot; {{ $listing->city }} @endif
        @if ($listing->salary_min || $listing->salary_max)
            &middot; Rs. {{ number_format($listing->salary_min ?? 0, 0) }}@if($listing->salary_max) – {{ number_format($listing->salary_max, 0) }}@endif
        @endif
    </p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('messages.careers.description_title') }}</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $listing->description }}</p>

        @if ($listing->requirements)
            <h2 class="mt-6 text-sm font-semibold text-slate-900 dark:text-white">{{ __('messages.careers.requirements_title') }}</h2>
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $listing->requirements }}</p>
        @endif
    </div>

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
        @guest
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.careers.apply_title') }}</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.sign_in_prompt') }}</p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('login') }}" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.careers.log_in') }}</a>
                <a href="{{ route('register') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('messages.careers.create_account') }}</a>
            </div>
        @else
            @if (! auth()->user()->isJobSeeker())
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.wrong_role') }}</p>
            @elseif ($hasApplied)
                <div class="flex items-center gap-2 text-sm font-medium text-brand-700 dark:text-brand-400">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('messages.careers.already_applied') }}
                </div>
                <a href="{{ route('job-seeker.applications.index') }}" class="mt-3 inline-flex text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">{{ __('messages.careers.view_my_applications') }}</a>
            @else
                @php $profile = auth()->user()->jobSeekerProfile; @endphp
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.careers.apply_title') }}</h2>

                @if ($profile && $profile->hasResume())
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.resume_saved_prefix') }} <span class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->resume_original_name }}</span>{{ __('messages.careers.resume_saved_suffix') }}</p>
                @else
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.resume_upload_prompt') }}</p>
                @endif

                <form method="POST" action="{{ route('job-seeker.careers.apply', $listing) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('messages.careers.resume_label') }} {{ $profile && $profile->hasResume() ? __('messages.careers.resume_replace_hint') : '' }}</label>
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" {{ $profile && $profile->hasResume() ? '' : 'required' }}
                            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('messages.careers.cover_letter_label') }} <span class="text-slate-400 dark:text-slate-500">{{ __('messages.careers.optional') }}</span></label>
                        <textarea name="cover_letter" rows="4" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('cover_letter') }}</textarea>
                    </div>
                    <button type="submit" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.careers.submit_application') }}</button>
                </form>
            @endif
        @endguest
    </div>
</section>
@endsection
