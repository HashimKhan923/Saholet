@extends('layouts.admin')

@section('title', 'Contact messages — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Contact messages</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Submissions from the website's "Contact us" form.</p>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">From</th>
                    <th class="px-5 py-3">Subject</th>
                    <th class="px-5 py-3">Mail</th>
                    <th class="px-5 py-3">Received</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($messages as $message)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 {{ $message->read_at ? '' : 'bg-brand-50/40 dark:bg-brand-950/10' }}">
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $message->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $message->email }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $message->subject ?: '—' }}</td>
                        <td class="px-5 py-3">
                            @if ($message->mail_sent)
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Sent</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-950/40 dark:text-red-400">Failed</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $message->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.contact-messages.show', $message) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">View</a>
                                <x-confirm-form :action="route('admin.contact-messages.destroy', $message)" method="DELETE"
                                    button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Delete this message?" confirm-label="Delete" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No messages yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $messages->links() }}</div>
</section>
@endsection
