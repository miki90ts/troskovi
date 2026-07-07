<?php

namespace App\Concerns;

trait DebtValidationMessages
{
    public function debtValidationMessages(): array
    {
        return [
            'type.required' => 'Tip duga je obavezan.',
            'type.enum' => 'Izabrani tip duga nije ispravan.',
            'person_name.required' => 'Ime osobe je obavezno.',
            'person_name.string' => 'Ime osobe mora biti tekst.',
            'person_name.max' => 'Ime osobe ne sme biti duže od 255 karaktera.',
            'description.required' => 'Opis je obavezan.',
            'description.string' => 'Opis mora biti tekst.',
            'description.max' => 'Opis ne sme biti duži od 255 karaktera.',
            'amount.required' => 'Iznos je obavezan.',
            'amount.numeric' => 'Iznos mora biti broj.',
            'amount.gt' => 'Iznos mora biti veći od 0.',
            'currency_id.integer' => 'Valuta mora biti broj.',
            'currency_id.exists' => 'Izabrana valuta nije ispravna.',
            'date.required' => 'Datum je obavezan.',
            'date.date' => 'Datum nije ispravan.',
            'due_date.date' => 'Rok dospeća nije ispravan.',
            'due_date.after_or_equal' => 'Rok dospeća mora biti isti ili nakon datuma zaduženja.',
            'status.in' => 'Izabrani status nije ispravan.',
            'notes.string' => 'Napomena mora biti tekst.',
        ];
    }
}
