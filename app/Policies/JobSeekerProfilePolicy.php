<?php

namespace App\Policies;

use App\Models\JobSeekerProfile;
use App\Models\User;

class JobSeekerProfilePolicy
{
    public function viewResume(User $user, JobSeekerProfile $profile): bool
    {
        return $profile->user_id === $user->id || $user->isAdmin();
    }
}
