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
                'currency' => $this->fromAccount->currency,
            ],
            'to_account' => [
                'id' => $this->toAccount->id,
                'name' => $this->toAccount->name,
                'currency' => $this->toAccount->currency,
            ],
            'amount' => (float) $this->amount,
            'to_amount' => (float) ($this->to_amount ?? $this->amount),
            'exchange_rate' => (float) ($this->exchange_rate ?? 1),
            'base_amount' => (float) ($this->base_amount ?? $this->amount),
            'from_currency' => $this->whenLoaded('fromCurrency', fn() => $this->fromCurrency ? [
                'id' => $this->fromCurrency->id,
                'iso_code' => $this->fromCurrency->iso_code,
                'name' => $this->fromCurrency->name,
                'symbol' => $this->fromCurrency->symbol,
            ] : null),
            'to_currency' => $this->whenLoaded('toCurrency', fn() => $this->toCurrency ? [
                'id' => $this->toCurrency->id,
                'iso_code' => $this->toCurrency->iso_code,
                'name' => $this->toCurrency->name,
                'symbol' => $this->toCurrency->symbol,
            ] : null),
            'description' => $this->description,
            'date' => $this->date->toDateString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
