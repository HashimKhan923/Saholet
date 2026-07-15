<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $this->profileFor($request);

        return view('job-seeker.profile', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:120'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'current_position' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        $data['skills'] = $data['skills']
            ? array_values(array_filter(array_map('trim', explode(',', $data['skills']))))
            : [];

        $profile->fill($data)->save();

        return back()->with('success', 'Profile saved.');
    }

    public function storeResume(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $mimes = implode(',', config('careers.accepted_mimes'));
        $maxKb = config('careers.max_size_kb');

        $request->validate([
            'resume' => ['required', 'file', "mimes:$mimes", "max:$maxKb"],
        ]);

        $disk = config('careers.disk');
        $file = $request->file('resume');

        $profile->deleteResumeFile();

        $path = $file->store("resumes/{$profile->user_id}", $disk);

        $profile->update([
            'resume_path' => $path,
            'resume_original_name' => $file->getClientOriginalName(),
            'resume_mime_type' => $file->getClientMimeType(),
            'resume_size' => $file->getSize(),
            'resume_uploaded_at' => now(),
        ]);

        return back()->with('success', 'Resume uploaded.');
    }

    public function destroyResume(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $profile->deleteResumeFile();

        $profile->update([
            'resume_path' => null,
            'resume_original_name' => null,
            'resume_mime_type' => null,
            'resume_size' => null,
            'resume_uploaded_at' => null,
        ]);

        return back()->with('success', 'Resume removed.');
    }

    private function profileFor(Request $request): JobSeekerProfile
    {
        return JobSeekerProfile::firstOrCreate(['user_id' => $request->user()->id]);
    }
}
