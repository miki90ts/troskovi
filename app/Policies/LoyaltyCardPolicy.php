<?php

namespace App\Policies;

use App\Models\LoyaltyCard;
use App\Models\User;

class LoyaltyCardPolicy
{
    public function view(User $user, LoyaltyCard $loyaltyCard): bool
    {
        return $user->id === $loyaltyCard->user_id;
    }

    public function update(User $user, LoyaltyCard $loyaltyCard): bool
    {
        return $user->id === $loyaltyCard->user_id;
    }

    public function delete(User $user, LoyaltyCard $loyaltyCard): bool
    {
        return $user->id === $loyaltyCard->user_id;
    }
}
