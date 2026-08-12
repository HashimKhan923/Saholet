<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Models\CareerApplicationEvent;
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

        $application->load('jobSeeker.jobSeekerProfile', 'reviewer', 'events.causedBy');

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

        $fromStatus = $application->status;

        $application->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? $application->admin_notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($fromStatus !== $data['status']) {
            CareerApplicationEvent::create([
                'career_application_id' => $application->id,
                'type' => CareerApplicationEvent::TYPE_STATUS_CHANGED,
                'from_status' => $fromStatus,
                'to_status' => $data['status'],
                'caused_by' => $request->user()->id,
            ]);
        }

        if (! empty($data['admin_notes'])) {
            CareerApplicationEvent::create([
                'career_application_id' => $application->id,
                'type' => CareerApplicationEvent::TYPE_NOTE_ADDED,
                'note' => $data['admin_notes'],
                'caused_by' => $request->user()->id,
            ]);
        }

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
