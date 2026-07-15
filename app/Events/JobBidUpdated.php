<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobBidUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private const LABELS = [
        Bid::STATUS_PENDING => ['Pending', 'bg-amber-50 text-amber-700'],
        Bid::STATUS_ACCEPTED => ['Accepted', 'bg-brand-50 text-brand-700'],
        Bid::STATUS_REJECTED => ['Rejected', 'bg-slate-100 text-slate-500'],
        Bid::STATUS_WITHDRAWN => ['Withdrawn', 'bg-slate-100 text-slate-400'],
    ];

    public function __construct(public Bid $bid)
    {
        $this->bid->loadMissing('providerProfile.user', 'jobPost');
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('job.' . $this->bid->job_post_id)];
    }

    public function broadcastAs(): string
    {
        return 'bid.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        [$label, $classes] = self::LABELS[$this->bid->status] ?? [ucfirst($this->bid->status), 'bg-slate-100 text-slate-600'];
        $provider = $this->bid->providerProfile;

        return [
            'id' => $this->bid->id,
            'status' => $this->bid->status,
            'status_label' => $label,
            'status_classes' => $classes,
            'can_accept' => $this->bid->status === Bid::STATUS_PENDING && $this->bid->jobPost->isOpen(),
            'provider_name' => $provider->business_name ?: $provider->user->name,
            'city' => $provider->city ?: 'Pakistan',
            'experience_years' => $provider->experience_years,
            'amount' => number_format((float) $this->bid->amount, 0),
            'date_label' => $this->bid->dateLabel(),
            'time_label' => $this->bid->timeLabel(),
            'message' => $this->bid->message,
        ];
    }
}
