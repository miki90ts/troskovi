<?php

namespace App\Policies;

use App\Models\SpendingTarget;
use App\Models\User;

class SpendingTargetPolicy
{
    public function view(User $user, SpendingTarget $spendingTarget): bool
    {
        return $user->id === $spendingTarget->user_id;
    }

    public function update(User $user, SpendingTarget $spendingTarget): bool
    {
        return $user->id === $spendingTarget->user_id;
    }

    public function delete(User $user, SpendingTarget $spendingTarget): bool
    {
        return $user->id === $spendingTarget->user_id;
    }
}
