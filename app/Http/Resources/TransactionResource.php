<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'amount' => (float) $this->amount,
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'notes' => $this->notes,
            'payment_method' => $this->payment_method->value,
            'receipt_url' => $this->receipt_path
                ? url("/api/v1/transactions/{$this->id}/receipt")
                : null,
            'is_warranty' => (bool) $this->is_warranty,
            'warranty_expires_at' => $this->warranty_expires_at?->toDateString(),
            'warranty_is_expired' => $this->warranty_is_expired,
            'category' => $this->whenLoaded('category', fn() => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
                'color' => $this->category->color,
            ] : null),
            'bank_account' => $this->whenLoaded('bankAccount', fn() => $this->bankAccount ? [
                'id' => $this->bankAccount->id,
                'name' => $this->bankAccount->name,
            ] : null),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
