<?php

namespace App\Notifications;

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

        // Plain-text mail via the default mailer; best-effort (Notifier wraps in try/catch).
        Mail::raw($payload['body'] . ($payload['url'] ? "\n\n" . $payload['url'] : ''), function ($message) use ($recipient, $payload) {
            $message->to($recipient->email)->subject($payload['title']);
        });
    }

    private function recipientMailer(): ?string
    {
        return config('mail.default');
    }
}