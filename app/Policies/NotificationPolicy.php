<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function markRead(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
