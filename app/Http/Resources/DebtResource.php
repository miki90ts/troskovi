<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'person_name' => $this->person_name,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'exchange_rate' => (float) ($this->exchange_rate ?? 1),
            'base_amount' => (float) ($this->base_amount ?? $this->amount),
            'remaining_amount' => (float) $this->remaining_amount,
            'remaining_base_amount' => (float) ($this->remaining_base_amount ?? $this->remaining_amount),
            'paid_amount' => $this->paid_amount,
            'date' => $this->date->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,
            'is_overdue' => $this->is_overdue,
            'progress_percent' => $this->progress_percent,
            'notes' => $this->notes,
            'currency' => $this->whenLoaded('currency', fn() => $this->currency ? [
                'id' => $this->currency->id,
                'iso_code' => $this->currency->iso_code,
                'name' => $this->currency->name,
                'symbol' => $this->currency->symbol,
            ] : null),
            'linked_transactions_count' => $this->transactions_count ?? 0,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
