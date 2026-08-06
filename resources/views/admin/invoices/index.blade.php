@extends('layouts.admin')

@section('title', 'Invoices — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Invoices &amp; quotations</h1>
        </div>
        <a href="{{ route('admin.invoices.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">+ New document</a>
    </div>

    <div class="mt-6 flex gap-2">
        @foreach (['all' => 'All', 'invoice' => 'Invoices', 'quotation' => 'Quotations'] as $key => $label)
            <a href="{{ route('admin.invoices.index', ['type' => $key]) }}"
               class="inline-flex items-center rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ $type === $key ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:bg-slate-800 dark:text-slate-100">
                    <tr>
                        <th class="px-5 py-3">Document</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Total</th>
                        @if (auth()->user()->isAdmin())
                            <th class="px-5 py-3">Created by</th>
                        @endif
                        <th class="px-5 py-3">Created</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($invoices as $invoice)
                        @php($pendingRequest = $invoice->latestEditRequest?->isPending() ? $invoice->latestEditRequest : null)
                        <tr x-data="{ open: false }" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-900 dark:text-white">{{ $invoice->title ?: $invoice->reference }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $invoice->reference }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ ucfirst($invoice->type) }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $invoice->bill_to_name }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $invoice->total, 0) }}</td>
                            @if (auth()->user()->isAdmin())
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $invoice->createdBy?->name ?? '—' }}</td>
                            @endif
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                                {{ $invoice->created_at->format('d M Y') }}
                                <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $invoice->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a>

                                    @if (auth()->user()->isAdmin())
                                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Edit</a>
                                    @endif

                                    @if (auth()->user()->isAdmin() && $pendingRequest)
                                        <button type="button" @click="open = true" class="relative cursor-pointer whitespace-nowrap rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                                            Request
                                        </button>

                                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                            <div class="fixed inset-0 bg-slate-900/50" x-transition.opacity @click="open = false"></div>
                                            <div x-show="open" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
                                                <button type="button" @click="open = false" class="absolute right-4 top-4 cursor-pointer rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-200" aria-label="Close">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                                </button>
                                                <h3 class="pr-6 font-display text-base font-bold text-slate-900 dark:text-white">{{ $invoice->title ?: $invoice->reference }}</h3>
                                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Requested by {{ $pendingRequest->requestedBy?->name }} on {{ $pendingRequest->created_at->format('d M Y, h:i A') }}</p>
                                                <p class="mt-4 whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-900 dark:text-slate-300">{{ $pendingRequest->reason }}</p>
                                                <div class="mt-6 flex justify-end gap-2">
                                                    <form method="POST" action="{{ route('admin.invoice-edit-requests.decline', $pendingRequest) }}">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">Decline</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.invoice-edit-requests.approve', $pendingRequest) }}">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Approve</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (auth()->user()->isAdmin())
                                        <x-confirm-form :action="route('admin.invoices.destroy', $invoice)" method="DELETE"
                                            button-label="Delete" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                            title="Delete this document?" message="This permanently removes the document and its line items." confirm-label="Delete" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 7 : 6 }}" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No documents yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $invoices->links() }}
</section>
@endsection
