<?php

namespace App\Policies;

use App\Models\ProviderService;
use App\Models\User;

class ProviderServicePolicy
{
    public function update(User $user, ProviderService $providerService): bool
    {
        return $user->providerProfile && $providerService->provider_profile_id === $user->providerProfile->id;
    }

    public function delete(User $user, ProviderService $providerService): bool
    {
        return $user->providerProfile && $providerService->provider_profile_id === $user->providerProfile->id;
    }
}
