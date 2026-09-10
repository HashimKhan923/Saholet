<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Either the consumer who placed it or the provider fulfilling it. */
    public function view(User $user, Order $order): bool
    {
        return $order->isParticipant($user) || $user->isAdmin();
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->consumer_id === $user->id;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $order->isProviderUser($user);
    }

    public function review(User $user, Order $order): bool
    {
        return $order->consumer_id === $user->id;
    }
}
