<?php

namespace App\Concerns;

trait LoyaltyCardValidationMessages
{
    public function loyaltyCardValidationMessages(): array
    {
        return [
            'name.required' => 'Naziv kartice je obavezan.',
            'name.string' => 'Naziv kartice mora biti tekst.',
            'name.max' => 'Naziv kartice ne sme biti duži od 255 karaktera.',
            'card_number.required' => 'Broj kartice je obavezan.',
            'card_number.string' => 'Broj kartice mora biti tekst.',
            'card_number.max' => 'Broj kartice ne sme biti duži od 100 karaktera.',
            'notes.string' => 'Napomena mora biti tekst.',
            'notes.max' => 'Napomena ne sme biti duža od 1000 karaktera.',
            'color.string' => 'Boja mora biti tekst.',
            'color.max' => 'Boja ne sme biti duža od 7 karaktera.',
        ];
    }
}
