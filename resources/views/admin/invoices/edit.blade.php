@extends('layouts.admin')

@section('title', 'Edit ' . $invoice->title . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; {{ $invoice->title }}</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit {{ ucfirst($invoice->type) }}</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Ref: {{ $invoice->reference }} — changes are saved to this same document.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    @include('admin.invoices._form')
</section>
@endsection
