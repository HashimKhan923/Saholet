@extends('layouts.app')

@section('title', 'My jobs — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My jobs</h1>
        </div>
        <a href="{{ route('consumer.jobs.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ Post a job</a>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($jobs as $job)
            <a href="{{ route('consumer.jobs.show', $job) }}"
               class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $job->service->name }}</span>
                            <x-job-status :status="$job->status" />
                        </div>
                        <p class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">{{ $job->description }}</p>
                    </div>
                    <div class="text-right">
                        @if ($job->isOpen())
                            <p class="text-sm font-semibold text-brand-700 dark:text-brand-400">{{ $job->pending_bids_count }} {{ \Illuminate\Support\Str::plural('bid', $job->pending_bids_count) }}</p>
                        @endif
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $job->reference }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No jobs yet</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Post a job and let verified providers send you quotes.</p>
                <a href="{{ route('consumer.jobs.create') }}" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">Post a job</a>
            </div>
        @endforelse
    </div>

    {{ $jobs->links() }}
</section>
@endsection