@extends('layouts.admin')

@section('title', $listing->title . ' — applications — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.careers.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Job listings</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Applications — {{ $listing->title }}</h1>

    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Applicant</th>
                    <th class="px-5 py-3">Applied</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($applications as $application)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $application->jobSeeker->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $application->jobSeeker->email }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $application->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.careers.applications.show', [$listing, $application]) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No applications yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $applications->links() }}
</section>
@endsection
