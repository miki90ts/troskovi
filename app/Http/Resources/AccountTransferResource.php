<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_account' => [
                'id' => $this->fromAccount->id,
                'name' => $this->fromAccount->name,
            ],
            'to_account' => [
                'id' => $this->toAccount->id,
                'name' => $this->toAccount->name,
            ],
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'date' => $this->date->toDateString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
