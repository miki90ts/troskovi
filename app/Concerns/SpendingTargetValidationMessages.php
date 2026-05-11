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
            'category_id.integer' => 'Kategorija mora biti broj.',
            'category_id.exists' => 'Izabrana kategorija nije ispravna.',
            'is_active.boolean' => 'Polje aktivno mora biti tačno ili netačno.',
        ];
    }
}
