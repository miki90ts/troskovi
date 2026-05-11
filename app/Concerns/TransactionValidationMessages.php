<?php

namespace App\Concerns;

trait TransactionValidationMessages
{
    public function transactionValidationMessages(): array
    {
        return [
            'type.required' => 'Tip transakcije je obavezan.',
            'type.enum' => 'Izabrani tip transakcije nije ispravan.',
            'amount.required' => 'Iznos je obavezan.',
            'amount.numeric' => 'Iznos mora biti broj.',
            'amount.gt' => 'Iznos mora biti veći od 0.',
            'date.required' => 'Datum je obavezan.',
            'date.date' => 'Datum nije ispravan.',
            'description.required' => 'Opis je obavezan.',
            'description.string' => 'Opis mora biti tekst.',
            'description.max' => 'Opis ne sme biti duži od 255 karaktera.',
            'category_id.integer' => 'Kategorija mora biti broj.',
            'category_id.exists' => 'Izabrana kategorija nije ispravna.',
            'bank_account_id.integer' => 'Bankovni račun mora biti broj.',
            'bank_account_id.exists' => 'Izabrani bankovni račun nije ispravan.',
            'payment_method.required_if' => 'Način plaćanja je obavezan za trošak.',
            'payment_method.enum' => 'Izabrani način plaćanja nije ispravan.',
            'notes.string' => 'Napomena mora biti tekst.',
            'receipt.image' => 'Potvrda mora biti slika.',
            'receipt.max' => 'Potvrda ne sme biti veća od 1 MB.',
            'is_warranty.boolean' => 'Polje garancije mora biti tačno ili netačno.',
            'debt_id.exists' => 'Izabrani dug nije ispravan.',
        ];
    }
}
