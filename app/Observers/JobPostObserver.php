<?php

namespace App\Observers;

use App\Events\JobPostCreated;
use App\Events\JobPostStatusUpdated;
use App\Models\JobPost;

class JobPostObserver
{
    public function created(JobPost $jobPost): void
    {
        try {
            broadcast(new JobPostCreated($jobPost));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function updated(JobPost $jobPost): void
    {
        if (! $jobPost->wasChanged('status')) {
            return;
        }

        try {
            broadcast(new JobPostStatusUpdated($jobPost));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
