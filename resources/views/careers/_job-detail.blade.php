@php $headingTag = $headingTag ?? 'h1'; @endphp

<span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $listing->category->name }}</span>
<{{ $headingTag }} class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">{{ $listing->title }}</{{ $headingTag }}>
<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
    {{ ucfirst(str_replace('_', ' ', $listing->employment_type)) }}
    @if ($listing->is_remote) &middot; {{ __('messages.careers.remote_label') }} @endif
    @if ($listing->city) &middot; {{ $listing->city }} @endif
    @if ($listing->salary_min || $listing->salary_max)
        &middot; Rs. {{ number_format($listing->salary_min ?? 0, 0) }}@if($listing->salary_max) – {{ number_format($listing->salary_max, 0) }}@endif
    @endif
</p>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('messages.careers.description_title') }}</h2>
    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $listing->description }}</p>

    @if ($listing->requirements)
        <h2 class="mt-6 text-sm font-semibold text-slate-900 dark:text-white">{{ __('messages.careers.requirements_title') }}</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $listing->requirements }}</p>
    @endif
</div>
