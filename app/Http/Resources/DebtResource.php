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
            'remaining_amount' => (float) $this->remaining_amount,
            'paid_amount' => $this->paid_amount,
            'date' => $this->date->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,
            'is_overdue' => $this->is_overdue,
            'progress_percent' => $this->progress_percent,
            'notes' => $this->notes,
            'linked_transactions_count' => $this->transactions_count ?? 0,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
