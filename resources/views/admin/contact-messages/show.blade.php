@extends('layouts.admin')

@section('title', 'Message from ' . $contactMessage->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Contact messages</a>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $contactMessage->subject ?: 'No subject' }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $contactMessage->created_at->format('d M Y, h:i A') }}</p>
            </div>
            @if ($contactMessage->mail_sent)
                <span class="inline-flex shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Emailed successfully</span>
            @else
                <span class="inline-flex shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-950/40 dark:text-red-400">Email failed to send</span>
            @endif
        </div>

        <dl class="mt-6 grid gap-4 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2 dark:bg-slate-800/60">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Name</dt>
                <dd class="mt-0.5 text-slate-800 dark:text-slate-200">{{ $contactMessage->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Email</dt>
                <dd class="mt-0.5"><a href="mailto:{{ $contactMessage->email }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $contactMessage->email }}</a></dd>
            </div>
            @if ($contactMessage->phone)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Phone</dt>
                    <dd class="mt-0.5 text-slate-800 dark:text-slate-200">{{ $contactMessage->phone }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Message</h2>
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ $contactMessage->message }}</p>
        </div>

        <div class="mt-8 flex items-center gap-3">
            <a href="mailto:{{ $contactMessage->email }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Reply by email</a>
            <x-confirm-form :action="route('admin.contact-messages.destroy', $contactMessage)" method="DELETE"
                button-label="Delete" button-class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                title="Delete this message?" confirm-label="Delete" />
        </div>
    </div>
</section>
@endsection
