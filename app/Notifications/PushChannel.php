<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'push';
    }

    public function label(): string
    {
        return 'Push';
    }

    public function isAvailable(): bool
    {
        $cfg = config('notifications.channels.push');

        return ! empty($cfg['vapid_public_key']) && ! empty($cfg['vapid_private_key']);
    }

    public function deliver(User $recipient, array $payload): void
    {
        if (! $this->isAvailable()) {
            Log::warning('[notify:push] Push channel enabled but not configured — skipped.', [
                'to' => $recipient->id,
                'title' => $payload['title'],
            ]);
            return;
        }

        $subscriptions = $recipient->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            return;
        }

        $cfg = config('notifications.channels.push');

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $cfg['vapid_subject'],
                'publicKey' => $cfg['vapid_public_key'],
                'privateKey' => $cfg['vapid_private_key'],
            ],
        ]);

        $body = json_encode([
            'title' => $payload['title'],
            'body' => $payload['body'],
            'url' => $payload['url'] ?? '/',
            'tag' => $payload['type'] ?? null,
        ]);

        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding,
                ]),
                $body
            );
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                continue;
            }

            $endpoint = $report->getRequest()->getUri()->__toString();

            // Gone/expired subscription — stop trying it again.
            if (in_array($report->getResponse()?->getStatusCode(), [404, 410], true)) {
                $recipient->pushSubscriptions()->where('endpoint', $endpoint)->delete();
            } else {
                Log::warning('[notify:push] delivery failed', ['endpoint' => $endpoint, 'reason' => $report->getReason()]);
            }
        }
    }
}
