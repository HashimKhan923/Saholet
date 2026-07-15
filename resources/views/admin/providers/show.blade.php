@extends('layouts.admin')

@section('title', 'Review provider — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.providers.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Provider approvals</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $provider->user->name }}</h1>
        @switch($provider->status)
            @case('approved')
                <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Approved</span>
                @break
            @case('pending')
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Pending review</span>
                @break
            @case('rejected')
                <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">Rejected</span>
                @break
            @default
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Draft</span>
        @endswitch
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Details</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->user->email }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->user->phone ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Business</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->business_name ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->city ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Experience</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->experience_years }} yr</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">CNIC</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->cnic_number ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->address ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">About</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->bio ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Documents</h2>
                @if ($provider->documents->isEmpty())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No documents uploaded.</p>
                @else
                    <ul class="mt-4 space-y-2">
                        @foreach ($provider->documents as $doc)
                            <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 text-sm dark:bg-slate-800">
                                <div>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $doc->label() }}</span>
                                    <span class="ml-2 text-xs text-slate-400 dark:text-slate-500">{{ $doc->original_name }}</span>
                                </div>
                                <a href="{{ route('provider-documents.show', $doc) }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">View</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($provider->isRejected() && $provider->rejection_reason)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950/40">
                    <h2 class="font-display text-sm font-bold text-red-900 dark:text-red-300">Rejection reason</h2>
                    <p class="mt-1 text-sm text-red-800 dark:text-red-400">{{ $provider->rejection_reason }}</p>
                </div>
            @endif
        </div>

        {{-- Decision --}}
        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Decision</h2>

                @if ($provider->isPending())
                    <form method="POST" action="{{ route('admin.providers.approve', $provider) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('admin.providers.reject', $provider) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="rejection_reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reason for rejection</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                            class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('rejection_reason') }}</textarea>
                        <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">Reject</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        @if ($provider->isApproved())
                            Approved{{ $provider->reviewed_at ? ' on ' . $provider->reviewed_at->format('d M Y') : '' }}{{ $provider->reviewer ? ' by ' . $provider->reviewer->name : '' }}.
                        @elseif ($provider->isRejected())
                            Rejected{{ $provider->reviewed_at ? ' on ' . $provider->reviewed_at->format('d M Y') : '' }}. The provider can update and resubmit.
                        @else
                            This application hasn’t been submitted yet.
                        @endif
                    </p>
                @endif
            </div>
        </aside>
    </div>
</section>
@endsection