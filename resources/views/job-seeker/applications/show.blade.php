@extends('layouts.app')

@section('title', $application->listing->title . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('job-seeker.applications.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; My applications</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $application->listing->title }}</h1>
        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $application->listing->category->name }} · Applied {{ $application->created_at->format('d M Y') }}</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Resume</dt><dd class="font-medium text-slate-800 dark:text-slate-200"><a href="{{ route('career-applications.resume.show', $application) }}" target="_blank" rel="noopener" class="text-brand-700 underline hover:text-brand-800 dark:text-brand-400">{{ $application->resume_original_name }}</a></dd></div>
            @if ($application->cover_letter)
                <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Cover letter</dt><dd class="whitespace-pre-line font-medium text-slate-800 dark:text-slate-200">{{ $application->cover_letter }}</dd></div>
            @endif
        </dl>

        @if ($application->isWithdrawable())
            <div class="mt-6">
                <x-confirm-form :action="route('job-seeker.applications.withdraw', $application)"
                    button-label="Withdraw application" button-class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                    title="Withdraw this application?" message="You can't undo this." confirm-label="Withdraw" />
            </div>
        @endif
    </div>
</section>
@endsection
