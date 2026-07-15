<?php

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;

class JobPostPolicy
{
    public function view(User $user, JobPost $jobPost): bool
    {
        return $jobPost->consumer_id === $user->id;
    }

    public function cancel(User $user, JobPost $jobPost): bool
    {
        return $jobPost->consumer_id === $user->id;
    }

    public function acceptBid(User $user, JobPost $jobPost): bool
    {
        return $jobPost->consumer_id === $user->id;
    }
}
