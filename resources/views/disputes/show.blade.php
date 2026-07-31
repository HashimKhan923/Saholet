@extends('layouts.app')

@section('title', $dispute->reference . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-4 flex justify-end">
        <x-close-button href="{{ $backUrl }}" />
    </div>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">Dispute {{ $dispute->reference }}</h1>
        <x-dispute-status :status="$dispute->status" />
    </div>
    <p class="mt-1 text-sm text-slate-500">{{ $dispute->booking->service->name }} · {{ $dispute->booking->reference }}</p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Opened by</dt><dd class="font-medium text-slate-800">{{ $dispute->opener->name }} ({{ $dispute->opened_by_role }})</dd></div>
            <div><dt class="text-slate-500">Submitted</dt><dd class="font-medium text-slate-800">{{ $dispute->created_at->format('d M Y, g:i A') }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500">Reason</dt><dd class="font-medium text-slate-800">{{ $dispute->reason }}</dd></div>
        </dl>

        @if ($dispute->photos->isNotEmpty())
            <div class="mt-6">
                <dt class="text-sm text-slate-500">Photo evidence</dt>
                <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-5">
                    @foreach ($dispute->photos as $photo)
                        <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200">
                            <img src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover transition group-hover:scale-105">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! $dispute->isOpen())
            <div class="mt-6 rounded-xl bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-900">
                    @if ($dispute->isResolved())
                        Resolved — {{ $dispute->resolution === 'refund' ? 'refunded to customer' : 'released to provider' }}
                    @else
                        Dismissed
                    @endif
                </p>
                @if ($dispute->resolution_note)
                    <p class="mt-1 text-sm text-slate-600">{{ $dispute->resolution_note }}</p>
                @endif
                @if ($dispute->resolved_at)
                    <p class="mt-1 text-xs text-slate-400">{{ $dispute->resolved_at->format('d M Y, g:i A') }}</p>
                @endif
            </div>
        @else
            <p class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                This dispute is open and awaiting review by our team.
            </p>
        @endif
    </div>
</section>
@endsection