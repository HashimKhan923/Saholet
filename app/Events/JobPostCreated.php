<?php

namespace App\Events;

use App\Models\JobPost;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class JobPostCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public JobPost $jobPost)
    {
        $this->jobPost->loadMissing('service');
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        // Public: no bid amounts or consumer identity here, just enough to render a
        // job-board card — every eligible provider needs this instantly, no per-provider auth.
        return [new Channel('jobs')];
    }

    public function broadcastAs(): string
    {
        return 'job.created';
    }

/** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'job_id'       => $this->jobPost->id,
            'service_id'   => $this->jobPost->service_id,
            'service_name' => $this->jobPost->service->name,
            'description'  => Str::limit($this->jobPost->description, 110),
            'city'         => $this->jobPost->city,
            'budget'       => $this->jobPost->budget !== null ? number_format((float) $this->jobPost->budget, 0) : null,
            'budget_raw'   => $this->jobPost->budget !== null ? (float) $this->jobPost->budget : null,
            'preferred'    => $this->jobPost->preferred_date?->format('D, d M') ?? 'Flexible',
            'bids_count'   => 0,
            'photos_count' => 0,
            'posted'       => 'Just now',
            'created_ts'   => now()->timestamp,
            'url'          => route('provider.jobs.show', $this->jobPost),
        ];
    }
}
