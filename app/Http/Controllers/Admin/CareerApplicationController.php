<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Models\CareerListing;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CareerApplicationController extends Controller
{
    public function index(CareerListing $career): View
    {
        $applications = $career->applications()
            ->with('jobSeeker')
            ->latest()
            ->paginate(20);

        return view('admin.careers.applications.index', ['listing' => $career, 'applications' => $applications]);
    }

    public function show(CareerListing $career, CareerApplication $application): View
    {
        abort_unless($application->career_listing_id === $career->id, 404);

        $application->load('jobSeeker.jobSeekerProfile', 'reviewer');

        return view('admin.careers.applications.show', ['listing' => $career, 'application' => $application]);
    }

    public function updateStatus(Request $request, CareerListing $career, CareerApplication $application): RedirectResponse
    {
        abort_unless($application->career_listing_id === $career->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                CareerApplication::STATUS_UNDER_REVIEW,
                CareerApplication::STATUS_SHORTLISTED,
                CareerApplication::STATUS_INTERVIEW,
                CareerApplication::STATUS_REJECTED,
                CareerApplication::STATUS_HIRED,
            ])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? $application->admin_notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        app(Notifier::class)->notify(
            $application->jobSeeker,
            'career',
            'Your application status changed',
            $career->title . ' — ' . ucfirst(str_replace('_', ' ', $data['status'])),
            route('job-seeker.applications.show', $application)
        );

        return back()->with('success', 'Application updated.');
    }
}
