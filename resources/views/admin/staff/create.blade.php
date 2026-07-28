@extends('layouts.admin')

@section('title', 'Add staff member — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.staff.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Staff</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Add staff member</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create a login for an internal team member and choose exactly which admin pages they can use.</p>

    <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @include('admin.staff._form')
    </form>
</section>
@endsection
