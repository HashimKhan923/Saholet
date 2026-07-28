@extends('layouts.admin')

@section('title', 'Edit ' . $staff->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.staff.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Staff</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit {{ $staff->name }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update their details or adjust which admin pages they can access.</p>

    <form method="POST" action="{{ route('admin.staff.update', $staff) }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('PUT')
        @include('admin.staff._form')
    </form>
</section>
@endsection
