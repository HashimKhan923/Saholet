<?php

namespace App\Notifications;

use App\Models\User;

interface NotificationChannel
{
    /** Machine key, matches config('notifications.channels.{key}'). */
    public function key(): string;

    /** Human label. */
    public function label(): string;

    /** Whether this channel has what it needs to deliver. */
    public function isAvailable(): bool;

    /**
     * Deliver a notification. Must not throw for expected "not configured"
     * situations — log and return instead.
     *
     * @param array{type:string,title:string,body:string,url:?string} $payload
     */
    public function deliver(User $recipient, array $payload): void;
}