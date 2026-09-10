<?php

namespace App\Policies;

use App\Models\ProductPhoto;
use App\Models\User;

class ProductPhotoPolicy
{
    public function delete(User $user, ProductPhoto $photo): bool
    {
        $photo->loadMissing('product');

        return $user->providerProfile && $photo->product->provider_profile_id === $user->providerProfile->id;
    }
}
