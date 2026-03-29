<?php

namespace App\Services;

use App\Models\LoyaltyCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LoyaltyCardService
{
    public function list(User $user): Collection
    {
        return $user->loyaltyCards()
            ->orderBy('name')
            ->get();
    }

    public function create(User $user, array $data): LoyaltyCard
    {
        return $user->loyaltyCards()->create($data);
    }

    public function update(LoyaltyCard $loyaltyCard, array $data): LoyaltyCard
    {
        $loyaltyCard->update($data);

        return $loyaltyCard->fresh();
    }

    public function delete(LoyaltyCard $loyaltyCard): void
    {
        $loyaltyCard->delete();
    }
}
