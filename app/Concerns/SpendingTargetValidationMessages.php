<?php

namespace App\Concerns;

trait SpendingTargetValidationMessages
{
    public function spendingTargetValidationMessages(): array
    {
        return [
            'period.required' => 'Period budžeta je obavezan.',
            'period.enum' => 'Izabrani period budžeta nije ispravan.',
            'target_amount.required' => 'Ciljni iznos je obavezan.',
            'target_amount.numeric' => 'Ciljni iznos mora biti broj.',
            'target_amount.gt' => 'Ciljni iznos mora biti veći od 0.',
            'currency_id.integer' => 'Valuta mora biti broj.',
            'currency_id.exists' => 'Izabrana valuta nije ispravna.',
            'category_id.integer' => 'Kategorija mora biti broj.',
            'category_id.exists' => 'Izabrana kategorija nije ispravna.',
            'is_active.boolean' => 'Polje aktivno mora biti tačno ili netačno.',
        ];
    }
}
