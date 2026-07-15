@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">
            {{ __('messages.job_seeker_dashboard.badge') }}
        </span>
        <h1 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
            {{ __('messages.job_seeker_dashboard.welcome', ['name' => auth()->user()->name]) }}
        </h1>
        <p class="mt-2 max-w-prose text-sm leading-relaxed text-slate-600 dark:text-slate-400">
            {{ __('messages.job_seeker_dashboard.subtitle') }}
        </p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('careers.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.job_seeker_dashboard.browse_careers') }}</a>
            <a href="{{ route('job-seeker.profile.edit') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('messages.job_seeker_dashboard.edit_profile') }}</a>
        </div>
    </div>

    @if (! $profile || ! $profile->hasResume())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-400/90">
            {{ __('messages.job_seeker_dashboard.no_resume_warning') }} <a href="{{ route('job-seeker.profile.edit') }}" class="font-semibold underline">{{ __('messages.job_seeker_dashboard.add_resume_now') }}</a> {{ __('messages.job_seeker_dashboard.add_resume_hint') }}
        </div>
    @endif

    <div class="mt-8">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.job_seeker_dashboard.recent_applications') }}</h2>
            <a href="{{ route('job-seeker.applications.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">{{ __('messages.job_seeker_dashboard.view_all') }}</a>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($applications as $application)
                <a href="{{ route('job-seeker.applications.show', $application) }}"
                   class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $application->listing->title }}</span>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $application->listing->category->name }}</p>
                        </div>
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('messages.job_seeker_dashboard.empty_title') }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('messages.job_seeker_dashboard.empty_desc') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
