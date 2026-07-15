<?php

namespace App\Policies;

use App\Models\ProviderPortfolioPhoto;
use App\Models\User;

class ProviderPortfolioPhotoPolicy
{
    public function delete(User $user, ProviderPortfolioPhoto $photo): bool
    {
        return $user->providerProfile && $photo->provider_profile_id === $user->providerProfile->id;
    }
}
