<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\JobSeekerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CareerResumeController extends Controller
{
    public function showProfile(Request $request, JobSeekerProfile $jobSeekerProfile)
    {
        $this->authorize('viewResume', $jobSeekerProfile);
        abort_unless($jobSeekerProfile->resume_path, 404);

        $disk = config('careers.disk');
        abort_unless(Storage::disk($disk)->exists($jobSeekerProfile->resume_path), 404);

        return Storage::disk($disk)->response($jobSeekerProfile->resume_path, $jobSeekerProfile->resume_original_name);
    }

    public function showApplication(Request $request, CareerApplication $careerApplication)
    {
        $this->authorize('viewResume', $careerApplication);

        $disk = config('careers.disk');
        abort_unless(Storage::disk($disk)->exists($careerApplication->resume_path), 404);

        return Storage::disk($disk)->response($careerApplication->resume_path, $careerApplication->resume_original_name);
    }
}
