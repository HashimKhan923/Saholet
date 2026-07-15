<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function view(User $user, Contract $contract): bool
    {
        return $contract->consumer_id === $user->id || $user->isAdmin();
    }

    public function accept(User $user, Contract $contract): bool
    {
        return $contract->consumer_id === $user->id;
    }

    public function reject(User $user, Contract $contract): bool
    {
        return $contract->consumer_id === $user->id;
    }

    public function cancel(User $user, Contract $contract): bool
    {
        return $contract->consumer_id === $user->id;
    }

    public function pay(User $user, Contract $contract): bool
    {
        return $contract->consumer_id === $user->id;
    }

    public function viewReceipt(User $user, Contract $contract): bool
    {
        return $contract->consumer_id === $user->id || $user->isAdmin();
    }
}
