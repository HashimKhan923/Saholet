@extends('layouts.admin')

@section('title', $profile->user->name . ' — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.talent.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Job seekers</a>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="mt-4 flex items-center gap-4">
        <x-avatar :url="$profile->user->avatar_url" :name="$profile->user->name" size="lg" />
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $profile->user->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $profile->headline ?: ($profile->current_position ?: 'Job seeker') }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Contact</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->user->email }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->user->phone ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Current position</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->current_position ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Experience</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->experience_years !== null ? $profile->experience_years . ' yr' : '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Qualification</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->qualification ? \App\Models\JobSeekerProfile::QUALIFICATIONS[$profile->qualification] ?? $profile->qualification : '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->address ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->city ?: '—' }}</dd></div>
                    @if ($profile->linkedin_url)
                        <div><dt class="text-slate-500 dark:text-slate-400">LinkedIn / portfolio</dt><dd class="font-medium text-slate-800 dark:text-slate-200"><a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" class="text-brand-700 underline hover:text-brand-800 dark:text-brand-400">{{ $profile->linkedin_url }}</a></dd></div>
                    @endif
                    @if ($profile->skills)
                        <div class="sm:col-span-2">
                            <dt class="text-slate-500 dark:text-slate-400">Skills</dt>
                            <dd class="mt-1.5 flex flex-wrap gap-1.5">
                                @foreach ($profile->skills as $skill)
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">{{ $skill }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($profile->bio)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">About</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $profile->bio }}</p>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Resume</h2>
                @if ($profile->hasResume())
                    <a href="{{ route('job-seeker.resume.show', $profile) }}" target="_blank" rel="noopener"
                       class="mt-3 inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ $profile->resume_original_name }}
                    </a>
                @else
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No resume uploaded.</p>
                @endif
            </div>
        </div>

        <aside class="lg:col-span-1">
            @if ($profile->user->canBeDeleted() && ! $profile->user->isDeleted())
                <div class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm dark:border-red-900 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-red-700 dark:text-red-400">Delete account</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Wipes their name, email, phone and avatar and disables login — this can't be undone. Their applications stay on record.</p>
                    <div class="mt-4">
                        <x-confirm-form :action="route('admin.users.destroy', $profile->user)" method="DELETE"
                            button-label="Delete account" button-class="w-full rounded-lg border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                            title="Delete this job seeker's account?" message="Their name, email, phone and avatar will be wiped and login disabled — this can't be undone." confirm-label="Delete" />
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
