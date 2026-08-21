<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoChannel implements NotificationChannel
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    public function key(): string
    {
        return 'expo_push';
    }

    public function label(): string
    {
        return 'Mobile Push';
    }

    public function isAvailable(): bool
    {
        // No credentials needed here — Expo's push endpoint is public, keyed by
        // the device token itself. EAS holds the FCM/APNs credentials.
        return true;
    }

    public function deliver(User $recipient, array $payload): void
    {
        $tokens = $recipient->deviceTokens;

        if ($tokens->isEmpty()) {
            return;
        }

        $messages = $tokens->map(fn ($deviceToken) => [
            'to' => $deviceToken->token,
            'title' => $payload['title'],
            'body' => $payload['body'],
            'data' => ['url' => $payload['url'] ?? null, 'type' => $payload['type'] ?? null],
            'sound' => 'default',
            'channelId' => 'default',
        ])->values();

        try {
            $response = Http::post(self::ENDPOINT, $messages->all());
        } catch (\Throwable $e) {
            Log::warning('[notify:expo_push] request failed', ['error' => $e->getMessage()]);
            return;
        }

        if (! $response->successful()) {
            Log::warning('[notify:expo_push] delivery failed', ['status' => $response->status(), 'body' => $response->body()]);
            return;
        }

        $tickets = $response->json('data', []);

        foreach ($tickets as $index => $ticket) {
            if (($ticket['status'] ?? null) !== 'error') {
                continue;
            }

            $deviceToken = $tokens->get($index);
            $errorCode = $ticket['details']['error'] ?? null;

            if ($errorCode === 'DeviceNotRegistered' && $deviceToken) {
                $deviceToken->delete();
            } else {
                Log::warning('[notify:expo_push] ticket error', ['token' => $deviceToken?->token, 'error' => $errorCode]);
            }
        }
    }
}
