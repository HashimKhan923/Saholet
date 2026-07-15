<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'log';
    }

    public function label(): string
    {
        return 'Log';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function deliver(User $recipient, array $payload): void
    {
        Log::info('[notify] ' . $payload['title'], [
            'to' => $recipient->email,
            'type' => $payload['type'],
            'body' => $payload['body'],
            'url' => $payload['url'],
        ]);
    }
}