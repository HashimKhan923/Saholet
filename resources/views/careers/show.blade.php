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

    @include('careers._job-detail', ['listing' => $listing, 'headingTag' => 'h1'])
    @include('careers._apply-panel', ['listing' => $listing, 'hasApplied' => $hasApplied])
</section>
@endsection
