<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\User;

class BidPolicy
{
    public function update(User $user, Bid $bid): bool
    {
        return $user->providerProfile && $bid->provider_profile_id === $user->providerProfile->id;
    }

    public function delete(User $user, Bid $bid): bool
    {
        return $user->providerProfile && $bid->provider_profile_id === $user->providerProfile->id;
    }
}
