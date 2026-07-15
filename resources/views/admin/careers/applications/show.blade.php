@extends('layouts.admin')

@section('title', 'Review application — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.careers.applications.index', $listing) }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Applications</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $application->jobSeeker->name }}</h1>
        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Applied for {{ $listing->title }} on {{ $application->created_at->format('d M Y') }}</p>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Contact</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $application->jobSeeker->email }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $application->jobSeeker->phone ?: '—' }}</dd></div>
                    @if ($application->jobSeeker->jobSeekerProfile)
                        @php $p = $application->jobSeeker->jobSeekerProfile; @endphp
                        <div><dt class="text-slate-500 dark:text-slate-400">Headline</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $p->headline ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Experience</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $p->experience_years }} yr</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $p->city ?: '—' }}</dd></div>
                        @if ($p->skills)
                            <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Skills</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ implode(', ', $p->skills) }}</dd></div>
                        @endif
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Resume</h2>
                <a href="{{ route('career-applications.resume.show', $application) }}" target="_blank" rel="noopener"
                   class="mt-3 inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ $application->resume_original_name }}
                </a>
            </div>

            @if ($application->cover_letter)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Cover letter</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $application->cover_letter }}</p>
                </div>
            @endif

            @if ($application->admin_notes)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Notes</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $application->admin_notes }}</p>
                    @if ($application->reviewer)
                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Last updated by {{ $application->reviewer->name }} on {{ $application->reviewed_at?->format('d M Y') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Update status</h2>

                <form method="POST" action="{{ route('admin.careers.applications.status', [$listing, $application]) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="status" required
                        class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        @foreach (['under_review', 'shortlisted', 'interview', 'rejected', 'hired'] as $status)
                            <option value="{{ $status }}" @selected($application->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <textarea name="admin_notes" rows="3" placeholder="Internal notes (optional)"
                        class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('admin_notes') }}</textarea>
                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Save status</button>
                </form>
            </div>
        </aside>
    </div>
</section>
@endsection
