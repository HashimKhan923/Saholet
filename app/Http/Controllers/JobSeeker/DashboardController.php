<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->jobSeekerProfile;

        $applications = CareerApplication::with('listing.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('job-seeker.dashboard', compact('profile', 'applications'));
    }
}
