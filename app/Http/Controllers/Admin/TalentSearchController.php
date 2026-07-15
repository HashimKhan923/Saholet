<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TalentSearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $city = trim((string) $request->query('city', ''));
        $skill = trim((string) $request->query('skill', ''));
        $minExperience = $request->query('min_experience');

        $profiles = JobSeekerProfile::query()
            ->with('user')
            ->whereHas('user', fn ($query) => $query->whereNull('suspended_at'))
            ->whereNotNull('resume_path')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('headline', 'like', "%{$q}%")
                        ->orWhere('current_position', 'like', "%{$q}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($city !== '', fn ($query) => $query->where('city', 'like', "%{$city}%"))
            ->when($skill !== '', fn ($query) => $query->where('skills', 'like', "%{$skill}%"))
            ->when($minExperience !== null && $minExperience !== '', fn ($query) => $query->where('experience_years', '>=', (int) $minExperience))
            ->latest('resume_uploaded_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'total' => JobSeekerProfile::whereNotNull('resume_path')->count(),
            'with_experience' => JobSeekerProfile::whereNotNull('resume_path')->where('experience_years', '>=', 2)->count(),
            'cities' => JobSeekerProfile::whereNotNull('resume_path')->distinct('city')->count('city'),
        ];

        return view('admin.talent.index', [
            'profiles' => $profiles,
            'counts' => $counts,
            'q' => $q,
            'city' => $city,
            'skill' => $skill,
            'minExperience' => $minExperience,
        ]);
    }
}
