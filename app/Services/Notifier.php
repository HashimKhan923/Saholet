<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Jobs\DeliverNotificationChannels;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class Notifier
{
    /**
     * Write the in-app notification and fan out to enabled channels.
     * Entirely best-effort: never throws into the caller.
     */
    public function notify(?User $recipient, string $type, string $title, string $body, ?string $url = null): void
    {
        if (! $recipient) {
            return;
        }

        $this->deliver($recipient, $type, $title, $body, $url);
    }

    /** Notify every admin user (e.g. a new contract/application landed in their queue). */
    public function notifyAdmins(string $type, string $title, string $body, ?string $url = null): void
    {
        User::where('role', User::ROLE_ADMIN)->get()->each(
            fn (User $admin) => $this->deliver($admin, $type, $title, $body, $url)
        );
    }

    private function deliver(User $recipient, string $type, string $title, string $body, ?string $url): void
    {
        try {
            $notification = Notification::create([
                'user_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return;
        }

        try {
            $unreadCount = Notification::where('user_id', $recipient->id)->unread()->count();
            broadcast(new NotificationCreated($notification, $unreadCount));
        } catch (\Throwable $e) {
            Log::warning('[notify] realtime broadcast failed', ['error' => $e->getMessage()]);
        }

        $payload = ['type' => $type, 'title' => $title, 'body' => $body, 'url' => $url];

        try {
            DeliverNotificationChannels::dispatch($recipient, $payload);
        } catch (\Throwable $e) {
            Log::warning('[notify] failed to queue channel delivery', ['error' => $e->getMessage()]);
        }
    }
}