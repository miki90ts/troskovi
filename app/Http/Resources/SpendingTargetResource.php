<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpendingTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period' => $this->period->value,
            'target_amount' => (float) $this->target_amount,
            'is_active' => $this->is_active,
            'scope' => $this->category_id ? 'category' : 'overall',
            'currency' => $this->whenLoaded('currency', fn () => $this->currency ? [
                'id' => $this->currency->id,
                'iso_code' => $this->currency->iso_code,
                'name' => $this->currency->name,
                'symbol' => $this->currency->symbol,
            ] : null),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
                'color' => $this->category->color,
            ] : null),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
