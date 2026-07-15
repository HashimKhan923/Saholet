@extends('layouts.app')

@section('title', 'My applications — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('job-seeker.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My applications</h1>
        </div>
        <a href="{{ route('careers.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Browse careers</a>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($applications as $application)
            <a href="{{ route('job-seeker.applications.show', $application) }}"
               class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $application->listing->title }}</span>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $application->listing->category->name }} · Applied {{ $application->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No applications yet</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Browse open roles and apply to get started.</p>
                <a href="{{ route('careers.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">Browse careers</a>
            </div>
        @endforelse
    </div>

    {{ $applications->links() }}
</section>
@endsection
