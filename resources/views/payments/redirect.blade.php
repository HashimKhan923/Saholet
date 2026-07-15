@extends('layouts.app')

@section('title', 'Redirecting to payment gateway — ' . config('app.name'))

@section('content')
<section class="mx-auto flex min-h-[60vh] max-w-md flex-col items-center justify-center px-4 text-center sm:px-6 lg:px-8">
    <svg class="h-10 w-10 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/>
        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <h1 class="mt-6 font-display text-xl font-bold text-slate-900 dark:text-white">Redirecting you to your payment provider…</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Please don't close this page. You'll be brought back here automatically once payment is complete.</p>

    <form id="gateway-redirect-form" method="POST" action="{{ $redirectUrl }}" class="hidden">
        @foreach ($redirectFields as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>
</section>

<script>
    document.getElementById('gateway-redirect-form').submit();
</script>
@endsection
