@extends('layouts.app')

@section('title', 'My profile — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('job-seeker.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Dashboard</a>
    <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">My profile</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Keep this up to date — it's reused every time you apply.</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Resume --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Resume</h2>

        @if ($profile->hasResume())
            <div class="mt-3 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 text-sm dark:bg-slate-800">
                <div>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->resume_original_name }}</span>
                    <span class="ml-2 text-xs text-slate-400 dark:text-slate-500">{{ number_format($profile->resume_size / 1024, 0) }} KB</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ $profile->resumeUrl() }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">View</a>
                    <x-confirm-form :action="route('job-seeker.profile.resume.destroy')" method="DELETE"
                        button-label="Remove" button-class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40"
                        title="Remove your resume?" confirm-label="Remove" />
                </div>
            </div>
        @else
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No resume uploaded yet.</p>
        @endif

        <form method="POST" action="{{ route('job-seeker.profile.resume.store') }}" enctype="multipart/form-data" class="mt-4 flex items-center gap-2">
            @csrf
            <input type="file" name="resume" accept=".pdf,.doc,.docx" required
                class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <button type="submit" class="shrink-0 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ $profile->hasResume() ? 'Replace' : 'Upload' }}</button>
        </form>
        <x-field-error name="resume" />
        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">PDF, DOC or DOCX — up to {{ number_format(config('careers.max_size_kb') / 1024, 1) }} MB.</p>
    </div>

    {{-- Details --}}
    <form method="POST" action="{{ route('job-seeker.profile.update') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @csrf
        @method('PUT')

        <div>
            <label for="headline" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Headline</label>
            <input id="headline" name="headline" type="text" value="{{ old('headline', $profile->headline) }}" placeholder="e.g. Experienced electrician"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium text-slate-700 dark:text-slate-200">About you</label>
            <textarea id="bio" name="bio" rows="4" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('bio', $profile->bio) }}</textarea>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="current_position" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Current position <span class="text-slate-400 dark:text-slate-500">(opt)</span></label>
                <input id="current_position" name="current_position" type="text" value="{{ old('current_position', $profile->current_position) }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label for="experience_years" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Years of experience</label>
                <input id="experience_years" name="experience_years" type="number" min="0" max="60" required value="{{ old('experience_years', $profile->experience_years) }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </div>

        <div>
            <label for="city" class="block text-sm font-medium text-slate-700 dark:text-slate-200">City</label>
            <input id="city" name="city" type="text" value="{{ old('city', $profile->city) }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>

        <div x-data="{
                skills: {{ Illuminate\Support\Js::from(old('skills', $profile->skills ?? [])) }},
                draft: '',
                add() {
                    const value = this.draft.trim();
                    if (value && !this.skills.includes(value)) {
                        this.skills.push(value);
                    }
                    this.draft = '';
                },
                remove(index) {
                    this.skills.splice(index, 1);
                },
            }">
            <label for="skill-input" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Skills</label>
            <div class="mt-1.5 flex flex-wrap items-center gap-2 rounded-lg border border-slate-300 px-3 py-2.5 shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-200 dark:border-slate-700 dark:bg-slate-900">
                <template x-for="(skill, index) in skills" :key="skill">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">
                        <span x-text="skill"></span>
                        <button type="button" @click="remove(index)" class="text-brand-400 hover:text-brand-700 dark:hover:text-brand-200" aria-label="Remove skill">&times;</button>
                    </span>
                </template>
                <input id="skill-input" type="text" x-model="draft"
                    @keydown.enter.prevent="add()"
                    @keydown="if ($event.key === ',') { $event.preventDefault(); add(); }"
                    @blur="add()"
                    placeholder="Type a skill and press Enter"
                    class="min-w-40 flex-1 border-0 p-0.5 text-sm text-slate-900 outline-none focus:ring-0 dark:bg-transparent dark:text-white">
            </div>
            <template x-for="skill in skills" :key="skill">
                <input type="hidden" name="skills[]" :value="skill">
            </template>
            <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Press Enter (or comma) after each skill to add it.</p>
        </div>

        <div>
            <label for="linkedin_url" class="block text-sm font-medium text-slate-700 dark:text-slate-200">LinkedIn / portfolio URL <span class="text-slate-400 dark:text-slate-500">(opt)</span></label>
            <input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" placeholder="https://…"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Save profile</button>
    </form>
</section>
@endsection
