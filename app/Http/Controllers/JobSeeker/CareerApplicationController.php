<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Models\CareerListing;
use App\Models\JobSeekerProfile;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CareerApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = CareerApplication::with('listing.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('job-seeker.applications.index', compact('applications'));
    }

    public function show(Request $request, CareerApplication $application): View
    {
        $this->authorize('view', $application);

        $application->load('listing.category');

        return view('job-seeker.applications.show', compact('application'));
    }

    public function store(Request $request, CareerListing $listing): RedirectResponse
    {
        abort_unless($listing->isOpen(), 404);

        if ($listing->applications()->where('user_id', $request->user()->id)->exists()) {
            return back()->with('error', 'You’ve already applied to this listing.');
        }

        $profile = JobSeekerProfile::firstOrCreate(['user_id' => $request->user()->id]);

        $data = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:3000'],
            'resume' => [$profile->hasResume() ? 'nullable' : 'required', 'file', 'mimes:' . implode(',', config('careers.accepted_mimes')), 'max:' . config('careers.max_size_kb')],
        ]);

        $disk = config('careers.disk');

        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $resumePath = $file->store("resumes/{$profile->user_id}", $disk);
            $resumeName = $file->getClientOriginalName();
        } else {
            // Reuse a fresh copy of the profile's resume so each application keeps its own file.
            $resumeName = $profile->resume_original_name;
            $resumePath = 'resumes/' . $profile->user_id . '/' . uniqid('app_') . '_' . $resumeName;
            Storage::disk($disk)->copy($profile->resume_path, $resumePath);
        }

        $application = CareerApplication::create([
            'career_listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'resume_path' => $resumePath,
            'resume_original_name' => $resumeName,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status' => CareerApplication::STATUS_SUBMITTED,
        ]);

        app(Notifier::class)->notifyAdmins(
            'career',
            'New job application',
            $request->user()->name . ' applied for ' . $listing->title . '.',
            route('admin.careers.applications.show', [$listing, $application])
        );

        return redirect()
            ->route('job-seeker.applications.show', $application)
            ->with('success', 'Application submitted.');
    }

    public function withdraw(Request $request, CareerApplication $application): RedirectResponse
    {
        $this->authorize('withdraw', $application);

        if (! $application->isWithdrawable()) {
            return back()->with('error', 'This application can no longer be withdrawn.');
        }

        $application->update(['status' => CareerApplication::STATUS_WITHDRAWN]);

        return back()->with('success', 'Application withdrawn.');
    }
}
