<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NotificationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeliverNotificationChannels implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    /** @param array{type: string, title: string, body: string, url: ?string} $payload */
    public function __construct(
        public User $recipient,
        public array $payload,
    ) {}

    public function handle(NotificationManager $channels): void
    {
        foreach ($channels->enabled() as $channel) {
            try {
                $channel->deliver($this->recipient, $this->payload);
            } catch (\Throwable $e) {
                Log::warning('[notify] channel delivery failed: ' . $channel->key(), ['error' => $e->getMessage()]);
            }
        }
    }
}
