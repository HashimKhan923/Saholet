@extends('layouts.admin')

@section('title', 'Edit banner — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.banners.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Banners</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit banner</h1>

    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        @include('admin.banners._form', [
            'banner' => $banner,
            'action' => route('admin.banners.update', $banner),
            'method' => 'PUT',
            'submitLabel' => 'Save changes',
        ])
    </div>
</section>
@endsection
