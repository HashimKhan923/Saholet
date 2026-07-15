@extends('layouts.app')

@section('title', 'Leave a review — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('consumer.bookings.show', $booking) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Back to booking</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Rate your experience</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
        {{ $booking->service->name }} with {{ $booking->providerProfile->business_name ?: $booking->providerProfile->user->name }} · {{ $booking->reference }}
    </p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('consumer.reviews.store', $booking) }}" class="mt-6 space-y-6"
          x-data="{ rating: {{ (int) old('rating', 5) }} }">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <span class="block text-sm font-medium text-slate-700 dark:text-slate-200">Your rating</span>
            <input type="hidden" name="rating" :value="rating">
            <div class="mt-3 flex items-center gap-1">
                <template x-for="i in 5" :key="i">
                    <button type="button" @click="rating = i" class="p-0.5" :aria-label="i + ' stars'">
                        <svg viewBox="0 0 20 20" class="h-8 w-8 transition" :class="i <= rating ? 'text-amber-400' : 'text-slate-300'" fill="currentColor">
                            <path d="M10 1.6l2.5 5.1 5.6.8-4 3.9 1 5.6L10 14.4 5 17l1-5.6-4-3.9 5.6-.8L10 1.6z"/>
                        </svg>
                    </button>
                </template>
                <span class="ml-2 text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="rating + ' / 5'"></span>
            </div>

            <div class="mt-5">
                <label for="comment" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Comment <span class="text-slate-400 dark:text-slate-500">(optional)</span></label>
                <textarea id="comment" name="comment" rows="4" maxlength="1000" placeholder="Tell others about the service quality, punctuality, etc."
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('comment') }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Post review</button>
            <a href="{{ route('consumer.bookings.show', $booking) }}" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        </div>
    </form>
</section>
@endsection