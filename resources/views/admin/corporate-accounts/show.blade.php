@extends('layouts.admin')

@section('title', $account->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.corporate-accounts.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Corporate accounts</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $account->name }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Owner: {{ $account->owner->name }} ({{ $account->owner->email }})</p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500 dark:text-slate-400">Billing email</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $account->billing_email }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Billing phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $account->billing_phone ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $account->city ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 dark:text-slate-400">Consolidated spend</dt><dd class="font-medium text-slate-800 dark:text-slate-200">Rs. {{ number_format($totalSpend, 0) }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Billing address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $account->address ?: '—' }}</dd></div>
        </dl>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Team ({{ $account->members->count() }})</h2>
        <div class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($account->members as $member)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $member->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $member->email }}</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ ucfirst($member->corporate_role) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
