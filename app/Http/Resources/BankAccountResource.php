<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'bank_name' => $this->bank_name,
            'masked_account_number' => $this->masked_account_number,
            'currency' => $this->currency,
            'color' => $this->color,
            'icon' => $this->icon,
            'initial_balance' => (float) $this->initial_balance,
            'current_balance' => (float) $this->current_balance,
            'is_archived' => $this->is_archived,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
