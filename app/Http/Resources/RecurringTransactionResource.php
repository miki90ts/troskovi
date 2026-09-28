<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'amount' => (float) $this->amount,
            'currency' => $this->whenLoaded('currency', fn () => $this->currency ? [
                'id' => $this->currency->id,
                'iso_code' => $this->currency->iso_code,
                'name' => $this->currency->name,
                'symbol' => $this->currency->symbol,
            ] : null),
            'description' => $this->description,
            'frequency' => $this->frequency->value,
            'next_due_date' => $this->next_due_date->toDateString(),
            'last_processed_date' => $this->last_processed_date?->toDateString(),
            'payment_method' => $this->payment_method->value,
            'is_active' => $this->is_active,
            'linked_transactions_count' => (int) ($this->linked_transactions_count ?? 0),
            'can_delete' => (int) ($this->linked_transactions_count ?? 0) === 0,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
                'color' => $this->category->color,
            ] : null),
            'bank_account' => $this->whenLoaded('bankAccount', fn () => $this->bankAccount ? [
                'id' => $this->bankAccount->id,
                'name' => $this->bankAccount->name,
            ] : null),
            'debt' => $this->whenLoaded('debt', fn () => $this->debt ? [
                'id' => $this->debt->id,
                'person_name' => $this->debt->person_name,
                'type' => $this->debt->type->value,
                'remaining_amount' => (float) $this->debt->remaining_amount,
                'status' => $this->debt->status->value,
            ] : null),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
