@if (session('success'))
    <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    @guest
        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.careers.apply_title') }}</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.sign_in_prompt') }}</p>
        <div class="mt-4 flex gap-3">
            <a href="{{ route('login') }}" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.careers.log_in') }}</a>
            <a href="{{ route('register') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('messages.careers.create_account') }}</a>
        </div>
    @else
        @if (! auth()->user()->isJobSeeker())
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.wrong_role') }}</p>
        @elseif ($hasApplied)
            <div class="flex items-center gap-2 text-sm font-medium text-brand-700 dark:text-brand-400">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5 9-10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ __('messages.careers.already_applied') }}
            </div>
            <a href="{{ route('job-seeker.applications.index') }}" class="mt-3 inline-flex text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">{{ __('messages.careers.view_my_applications') }}</a>
        @else
            @php $profile = auth()->user()->jobSeekerProfile; @endphp
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ __('messages.careers.apply_title') }}</h2>

            @if ($profile && $profile->hasResume())
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.resume_saved_prefix') }} <span class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->resume_original_name }}</span>{{ __('messages.careers.resume_saved_suffix') }}</p>
            @else
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('messages.careers.resume_upload_prompt') }}</p>
            @endif

            <form method="POST" action="{{ route('job-seeker.careers.apply', $listing) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="_listing_id" value="{{ $listing->id }}">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('messages.careers.resume_label') }} {{ $profile && $profile->hasResume() ? __('messages.careers.resume_replace_hint') : '' }}</label>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx" {{ $profile && $profile->hasResume() ? '' : 'required' }}
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('messages.careers.cover_letter_label') }} <span class="text-slate-400 dark:text-slate-500">{{ __('messages.careers.optional') }}</span></label>
                    <textarea name="cover_letter" rows="4" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('cover_letter') }}</textarea>
                </div>
                <button type="submit" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('messages.careers.submit_application') }}</button>
            </form>
        @endif
    @endguest
</div>
