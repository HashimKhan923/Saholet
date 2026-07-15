@extends('layouts.provider')

@section('title', 'Portfolio — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Portfolio</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Show off completed work — before/after shots build trust and win more bookings. Up to 12 photos, visible on your public profile.</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    @php $remaining = 12 - $photos->count(); @endphp

    @if ($remaining > 0)
        <form method="POST" action="{{ route('provider.portfolio.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
              x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Add photos <span class="text-slate-400 dark:text-slate-500">({{ $remaining }} remaining)</span></label>
            <div class="mt-1.5">
                <x-photo-picker name="photos" :max="$remaining" />
            </div>
            <x-field-error name="photos" />
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Caption <span class="text-slate-400 dark:text-slate-500">(optional, applies to all photos in this upload)</span></label>
                <input type="text" name="caption" maxlength="255" placeholder="e.g. AC installation — DHA Phase 6"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <button type="submit" :disabled="submitting" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!submitting">Upload</span>
                <span x-show="submitting" x-cloak>Uploading…</span>
            </button>
        </form>
    @else
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-400/90">
            You've reached the 12-photo limit. Remove one below to add another.
        </div>
    @endif

    <div class="mt-8">
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Your photos ({{ $photos->count() }}/12)</h2>

        @if ($photos->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No portfolio photos yet</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Add a few examples of your best work above.</p>
            </div>
        @else
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($photos as $photo)
                    <div class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
                        <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?: $photo->original_name }}" class="h-full w-full object-cover">
                        @if ($photo->caption)
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/80 to-transparent px-2.5 py-2">
                                <p class="truncate text-[11px] font-medium text-white">{{ $photo->caption }}</p>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('provider.portfolio.destroy', $photo) }}" class="absolute right-1.5 top-1.5 opacity-0 transition group-hover:opacity-100">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900/70 text-white transition hover:bg-red-600" aria-label="Remove photo" onclick="return confirm('Remove this photo?')">
                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
