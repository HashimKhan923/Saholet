@extends('layouts.app')

@section('title', 'Emergency requests — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Emergency requests</h1>
        </div>
        <a href="{{ route('consumer.emergencies.create') }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">+ New request</a>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($requests as $req)
            <a href="{{ route('consumer.emergencies.show', $req) }}"
               class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $req->service->name }}</span>
                            <x-emergency-status :status="$req->status" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $req->city }} · {{ $req->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $req->reference }}</span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="font-display text-lg font-bold text-slate-900 dark:text-white">No emergency requests</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Need urgent help? Send a one-tap request and we’ll find a provider fast.</p>
                <a href="{{ route('consumer.emergencies.create') }}" class="mt-4 inline-flex items-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Request help</a>
            </div>
        @endforelse
    </div>
</section>
@endsection