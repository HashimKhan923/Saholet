<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return $subscription->consumer_id === $user->id || $user->isAdmin();
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        return $subscription->consumer_id === $user->id;
    }
}
