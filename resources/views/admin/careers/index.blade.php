@extends('layouts.admin')

@section('title', 'Job listings — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Job listings</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.career-categories.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Categories</a>
            <a href="{{ route('admin.careers.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New listing</a>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Title</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Applications</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($listings as $listing)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ $listing->title }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $listing->category->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                            <a href="{{ route('admin.careers.applications.index', $listing) }}" class="font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300">{{ $listing->applications_count }}</a>
                        </td>
                        <td class="px-5 py-3">
                            @switch($listing->status)
                                @case('open')
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Open</span>
                                    @break
                                @case('closed')
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Closed</span>
                                    @break
                                @case('filled')
                                    <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/40 dark:text-sky-400">Filled</span>
                                    @break
                                @default
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Draft</span>
                            @endswitch
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.careers.edit', $listing) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Edit</a>
                                <x-confirm-form :action="route('admin.careers.destroy', $listing)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this listing?" confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No job listings yet. Create your first one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $listings->links() }}
</section>
@endsection
