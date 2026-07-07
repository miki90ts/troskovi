<?php

namespace App\Concerns;

trait BankAccountValidationMessages
{
    public function bankAccountValidationMessages(): array
    {
        return [
            'name.required' => 'Naziv računa je obavezan.',
            'name.string' => 'Naziv računa mora biti tekst.',
            'name.max' => 'Naziv računa ne sme biti duži od 255 karaktera.',
            'bank_name.required' => 'Naziv banke je obavezan.',
            'bank_name.string' => 'Naziv banke mora biti tekst.',
            'bank_name.max' => 'Naziv banke ne sme biti duži od 255 karaktera.',
            'account_number.string' => 'Broj računa mora biti tekst.',
            'account_number.max' => 'Broj računa ne sme biti duži od 50 karaktera.',
            'currency.required' => 'Valuta je obavezna.',
            'currency.required_without' => 'Valuta je obavezna.',
            'currency.string' => 'Valuta mora biti tekst.',
            'currency.max' => 'Valuta ne sme biti duža od 10 karaktera.',
            'currency.exists' => 'Izabrana valuta nije ispravna.',
            'currency_id.required_without' => 'Valuta je obavezna.',
            'currency_id.integer' => 'Valuta mora biti broj.',
            'currency_id.exists' => 'Izabrana valuta nije ispravna.',
            'color.string' => 'Boja mora biti tekst.',
            'color.max' => 'Boja ne sme biti duža od 7 karaktera.',
            'icon.string' => 'Ikonica mora biti tekst.',
            'icon.max' => 'Ikonica ne sme biti duža od 50 karaktera.',
            'initial_balance.required' => 'Početno stanje je obavezno.',
            'initial_balance.numeric' => 'Početno stanje mora biti broj.',
            'initial_balance.min' => 'Početno stanje ne sme biti negativno.',
        ];
    }
}
