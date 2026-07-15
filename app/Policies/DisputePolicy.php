<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\User;

class DisputePolicy
{
    public function view(User $user, Dispute $dispute): bool
    {
        $dispute->loadMissing('booking');

        return $dispute->booking->isParticipant($user) || $user->isAdmin();
    }
}
