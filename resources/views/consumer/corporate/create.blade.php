@extends('layouts.app')

@section('title', 'Set up a company account — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Set up a company account</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Add teammates under one account so every booking, contract, and subscription they place rolls up into a single consolidated view — you stay the owner and billing contact.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            Please fix the highlighted fields below.
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.corporate.store') }}" class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Company name</label>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="name" />
        </div>

        <div>
            <label for="billing_email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Billing email</label>
            <input id="billing_email" name="billing_email" type="email" required value="{{ old('billing_email', auth()->user()->email) }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <x-field-error name="billing_email" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="billing_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Billing phone <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                <input id="billing_phone" name="billing_phone" type="text" value="{{ old('billing_phone') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label for="city" class="block text-sm font-medium text-slate-700 dark:text-slate-200">City <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                <input id="city" name="city" type="text" value="{{ old('city') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </div>

        <div>
            <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Billing address <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
            <textarea id="address" name="address" rows="2"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('address') }}</textarea>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Create company account</button>
    </form>
</section>
@endsection
