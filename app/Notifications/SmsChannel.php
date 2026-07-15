<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic REST SMS gateway: POSTs {api_key, sender, to, message} as JSON to
 * config('notifications.channels.sms.gateway'). Most Pakistani SMS gateways
 * (Telenor, Zong, and third-party resellers) accept this shape or something
 * close to it — adjust the payload/auth in send() to match your provider's
 * actual API contract if it differs.
 */
class SmsChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'sms';
    }

    public function label(): string
    {
        return 'SMS';
    }

    public function isAvailable(): bool
    {
        $cfg = config('notifications.channels.sms');

        return ! empty($cfg['gateway']) && ! empty($cfg['api_key']) && ! empty($cfg['sender']);
    }

    public function deliver(User $recipient, array $payload): void
    {
        if (! $this->isAvailable()) {
            // Announce rather than pretend to send.
            Log::warning('[notify:sms] SMS channel enabled but not configured — skipped.', [
                'to' => $recipient->phone,
                'title' => $payload['title'],
            ]);
            return;
        }

        if (blank($recipient->phone)) {
            return;
        }

        $cfg = config('notifications.channels.sms');
        $message = $payload['title'] . ' — ' . $payload['body'] . ($payload['url'] ? ' ' . $payload['url'] : '');

        $response = Http::timeout(10)->asJson()->post($cfg['gateway'], [
            'api_key' => $cfg['api_key'],
            'sender' => $cfg['sender'],
            'to' => $recipient->phone,
            'message' => $message,
        ]);

        if (! $response->successful()) {
            Log::warning('[notify:sms] gateway rejected the message', [
                'to' => $recipient->phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}