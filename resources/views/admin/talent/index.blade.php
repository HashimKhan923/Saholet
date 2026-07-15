@extends('layouts.admin')

@section('title', 'Talent search — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Talent search</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Search every job seeker with a resume on file — not just active applicants.</p>
    </div>

    <div class="mt-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Candidates with resume" :value="$counts['total']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke-linecap="round"/></svg>
        </x-stat-card>
        <x-stat-card label="2+ years experience" :value="$counts['with_experience']" tone="brand">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 8v4l2.5 2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
        </x-stat-card>
        <x-stat-card label="Cities covered" :value="$counts['cities']" tone="amber">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="10" r="3"/><path d="M12 2c4.4 0 8 3.6 8 8 0 5-8 12-8 12S4 15 4 10c0-4.4 3.6-8 8-8z" stroke-linejoin="round"/></svg>
        </x-stat-card>
    </div>

    <form method="GET" action="{{ route('admin.talent.index') }}" class="mt-6 flex flex-wrap items-center gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Name, headline, position…"
            class="w-56 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <input type="text" name="skill" value="{{ $skill }}" placeholder="Skill (e.g. electrician)"
            class="w-48 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <input type="text" name="city" value="{{ $city }}" placeholder="City"
            class="w-36 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <input type="number" min="0" name="min_experience" value="{{ $minExperience }}" placeholder="Min. years exp."
            class="w-40 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Search</button>
        @if ($q || $city || $skill || $minExperience)
            <a href="{{ route('admin.talent.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">Clear</a>
        @endif
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3">Candidate</th>
                    <th class="px-5 py-3">City</th>
                    <th class="px-5 py-3">Experience</th>
                    <th class="px-5 py-3">Skills</th>
                    <th class="px-5 py-3 text-right">Resume</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($profiles as $profile)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $profile->user->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $profile->headline ?? $profile->current_position ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $profile->city ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $profile->experience_years !== null ? $profile->experience_years . ' yrs' : '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse (array_slice($profile->skills ?? [], 0, 4) as $s)
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $s }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('job-seeker.resume.show', $profile) }}" target="_blank" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">View resume</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No candidates match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $profiles->links() }}</div>
</section>
@endsection
