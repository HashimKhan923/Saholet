@extends('layouts.admin')

@section('title', 'New invoice — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.invoices.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Invoices</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">New invoice or quotation</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Build a document line by line, then save, print, or download it as a PDF.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    @include('admin.invoices._form')
</section>
@endsection
