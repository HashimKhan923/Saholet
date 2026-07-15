<?php

namespace App\Policies;

use App\Models\CareerApplication;
use App\Models\User;

class CareerApplicationPolicy
{
    public function view(User $user, CareerApplication $application): bool
    {
        return $application->user_id === $user->id;
    }

    public function withdraw(User $user, CareerApplication $application): bool
    {
        return $application->user_id === $user->id;
    }

    public function viewResume(User $user, CareerApplication $application): bool
    {
        return $application->user_id === $user->id || $user->isAdmin();
    }
}
