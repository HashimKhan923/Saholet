<?php

namespace App\Notifications;

use App\Mail\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MailChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'mail';
    }

    public function label(): string
    {
        return 'Email';
    }

    public function isAvailable(): bool
    {
        // Uses the app's configured mailer. Laravel's default 'log' mailer needs no creds.
        return ! empty($this->recipientMailer());
    }

    public function deliver(User $recipient, array $payload): void
    {
        if (blank($recipient->email)) {
            return;
        }

        // Branded HTML mail via the default mailer; best-effort (Notifier wraps in try/catch).
        Mail::to($recipient->email)->send(
            new SystemNotification($payload['title'], $payload['body'], $payload['url'] ?? null)
        );
    }

    private function recipientMailer(): ?string
    {
        return config('mail.default');
    }
}