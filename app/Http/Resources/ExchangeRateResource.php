<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'rate' => (float) $this->rate,
            'currency' => $this->whenLoaded('currency', fn () => $this->currency ? [
                'id' => $this->currency->id,
                'iso_code' => $this->currency->iso_code,
                'name' => $this->currency->name,
                'symbol' => $this->currency->symbol,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
