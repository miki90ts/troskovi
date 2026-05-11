<?php

namespace App\Concerns;

trait TransferValidationMessages
{
    public function transferValidationMessages(): array
    {
        return [
            'from_account_id.required' => 'Izvorni račun je obavezan.',
            'from_account_id.exists' => 'Izabrani izvorni račun nije ispravan.',
            'to_account_id.required' => 'Odredišni račun je obavezan.',
            'to_account_id.exists' => 'Izabrani odredišni račun nije ispravan.',
            'to_account_id.different' => 'Odredišni račun mora biti različit od izvornog računa.',
            'amount.required' => 'Iznos je obavezan.',
            'amount.numeric' => 'Iznos mora biti broj.',
            'amount.gt' => 'Iznos mora biti veći od 0.',
            'description.string' => 'Opis mora biti tekst.',
            'description.max' => 'Opis ne sme biti duži od 255 karaktera.',
            'date.required' => 'Datum je obavezan.',
            'date.date' => 'Datum nije ispravan.',
        ];
    }
}
