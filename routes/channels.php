<?php

use App\Models\Booking;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('booking.{bookingId}', function (User $user, int $bookingId) {
    $booking = Booking::with('providerProfile')->find($bookingId);

    if (! $booking) {
        return false;
    }

    return $booking->isParticipant($user);
});

Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === $userId;
});

Broadcast::channel('job.{jobId}', function (User $user, int $jobId) {
    return JobPost::where('id', $jobId)->where('consumer_id', $user->id)->exists();
});

Broadcast::channel('provider.{profileId}', function (User $user, int $profileId) {
    $profile = $user->providerProfile;

    return $profile && $profile->id === $profileId && $profile->isApproved();
});