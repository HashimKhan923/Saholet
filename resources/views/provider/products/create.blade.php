@extends('layouts.provider')

@section('title', 'Add product — ' . config('app.name'))
@section('page_title', 'Add product')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('provider.products.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">
        <svg viewBox="0 0 24 24" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Products
    </a>
    <h1 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Add a product</h1>

    @if (! $profile->canSellProducts())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900/60 dark:bg-amber-950/30">
            <h2 class="font-display text-base font-bold text-amber-900 dark:text-amber-300">Set up your shop first</h2>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-400/90">You need at least one fulfillment method — delivery or self-pickup — before you can add products.</p>
            <a href="{{ route('provider.shop-settings.shipping.edit') }}" class="btn-shine mt-4 inline-flex items-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Go to shop settings</a>
        </div>
    @else
        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
                <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('provider.products.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
              x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @include('provider.products._form')

            <button type="submit" :disabled="submitting" class="btn-shine mt-6 inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Add product</span>
                <span x-show="submitting" x-cloak>Adding…</span>
            </button>
        </form>
    @endif
</section>
@endsection
