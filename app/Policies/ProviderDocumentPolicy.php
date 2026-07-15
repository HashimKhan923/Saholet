<?php

namespace App\Policies;

use App\Models\ProviderDocument;
use App\Models\User;

class ProviderDocumentPolicy
{
    public function view(User $user, ProviderDocument $document): bool
    {
        $document->loadMissing('providerProfile');

        return $document->providerProfile->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, ProviderDocument $document): bool
    {
        $document->loadMissing('providerProfile');

        return $document->providerProfile->user_id === $user->id;
    }
}
