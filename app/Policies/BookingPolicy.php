<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /** Either the consumer who booked or the assigned provider. */
    public function view(User $user, Booking $booking): bool
    {
        return $booking->isParticipant($user) || $user->isAdmin();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->consumer_id === $user->id;
    }

    public function pay(User $user, Booking $booking): bool
    {
        return $booking->consumer_id === $user->id;
    }

    public function release(User $user, Booking $booking): bool
    {
        return $booking->consumer_id === $user->id;
    }

    public function review(User $user, Booking $booking): bool
    {
        return $booking->consumer_id === $user->id;
    }

    public function dispute(User $user, Booking $booking): bool
    {
        return $booking->isParticipant($user);
    }

    public function shareLocation(User $user, Booking $booking): bool
    {
        return $booking->isProviderUser($user);
    }

    public function updateStatus(User $user, Booking $booking): bool
    {
        return $booking->isProviderUser($user);
    }

    public function viewReceipt(User $user, Booking $booking): bool
    {
        return $booking->consumer_id === $user->id || $user->isAdmin();
    }
}
