<?php

namespace App\Events;

use App\Models\JobPost;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPostStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public JobPost $jobPost)
    {
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        // Public: only carries job id + status, no sensitive data — every browsing
        // provider needs this the instant a job is taken, without per-job channel auth.
        return [new Channel('jobs')];
    }

    public function broadcastAs(): string
    {
        return 'job.status.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'job_id' => $this->jobPost->id,
            'status' => $this->jobPost->status,
            // Lets the awarded provider's own browser tell "you won" apart from
            // "someone else won" without a per-job authenticated channel.
            'accepted_provider_profile_id' => $this->jobPost->acceptedBid()?->provider_profile_id,
        ];
    }
}
