<?php

namespace App\Policies;

use App\Models\CorporateAccount;
use App\Models\User;

class CorporateAccountPolicy
{
    /** A user already in a company account can't start another one. */
    public function create(User $user): bool
    {
        return $user->corporate_account_id === null;
    }

    public function manageMembers(User $user, CorporateAccount $account): bool
    {
        return $user->corporate_account_id === $account->id
            && $user->corporate_role === CorporateAccount::ROLE_OWNER;
    }
}
