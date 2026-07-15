<?php

namespace App\Policies;

use App\Models\EmergencyRequest;
use App\Models\User;

class EmergencyRequestPolicy
{
    public function view(User $user, EmergencyRequest $emergencyRequest): bool
    {
        return $emergencyRequest->consumer_id === $user->id;
    }

    public function cancel(User $user, EmergencyRequest $emergencyRequest): bool
    {
        return $emergencyRequest->consumer_id === $user->id;
    }
}
