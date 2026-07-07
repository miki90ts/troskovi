<?php

namespace App\Concerns;

trait RecurringTransactionValidationMessages
{
    public function recurringTransactionValidationMessages(): array
    {
        return [
            'type.required' => 'Tip transakcije je obavezan.',
            'type.enum' => 'Izabrani tip transakcije nije ispravan.',
            'amount.required' => 'Iznos je obavezan.',
            'amount.numeric' => 'Iznos mora biti broj.',
            'amount.gt' => 'Iznos mora biti veći od 0.',
            'currency_id.integer' => 'Valuta mora biti broj.',
            'currency_id.exists' => 'Izabrana valuta nije ispravna.',
            'description.required' => 'Opis je obavezan.',
            'description.string' => 'Opis mora biti tekst.',
            'description.max' => 'Opis ne sme biti duži od 255 karaktera.',
            'frequency.required' => 'Učestalost je obavezna.',
            'frequency.enum' => 'Izabrana učestalost nije ispravna.',
            'next_due_date.required' => 'Datum izvršenja je obavezan.',
            'next_due_date.date' => 'Datum izvršenja nije ispravan.',
            'category_id.integer' => 'Kategorija mora biti broj.',
            'category_id.exists' => 'Izabrana kategorija nije ispravna.',
            'debt_id.exists' => 'Izabrani dug nije ispravan.',
            'bank_account_id.integer' => 'Bankovni račun mora biti broj.',
            'bank_account_id.exists' => 'Izabrani bankovni račun nije ispravan.',
            'payment_method.required_if' => 'Način plaćanja je obavezan za trošak.',
            'payment_method.enum' => 'Izabrani način plaćanja nije ispravan.',
            'is_active.boolean' => 'Polje aktivno mora biti tačno ili netačno.',
        ];
    }
}
