<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->providerProfile && $product->provider_profile_id === $user->providerProfile->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->providerProfile && $product->provider_profile_id === $user->providerProfile->id;
    }
}
