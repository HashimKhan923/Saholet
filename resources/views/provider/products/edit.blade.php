@extends('layouts.provider')

@section('title', 'Edit product — ' . config('app.name'))
@section('page_title', 'Edit product')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('provider.products.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">
        <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Products
    </a>
    <h1 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $product->name }}</h1>

    @if (! $product->is_active && $product->deactivation_reason)
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/40">
            <p class="text-sm font-bold text-red-800 dark:text-red-300">This product was deactivated by the Sahoulat team</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-400">Reason</p>
            <p class="mt-0.5 text-sm text-red-800 dark:text-red-300">{{ $product->deactivation_reason }}</p>
            @if ($product->reactivation_instructions)
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-400">What to do to reactivate it</p>
                <p class="mt-0.5 text-sm text-red-800 dark:text-red-300">{{ $product->reactivation_instructions }}</p>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.products.update', $product) }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('PUT')
        @include('provider.products._form')

        <div class="mt-6 flex items-center justify-between">
            <button type="submit" :disabled="submitting" class="btn-shine inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Save changes</span>
                <span x-show="submitting" x-cloak>Saving…</span>
            </button>

            <x-confirm-form :action="route('provider.products.destroy', $product)" method="DELETE"
                button-label="Delete product" button-class="rounded-lg border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30"
                title="Delete this product?" message="This can't be undone." confirm-label="Delete" />
        </div>
    </form>
</section>
@endsection
