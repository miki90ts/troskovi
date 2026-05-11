<?php

namespace App\Concerns;

trait CategoryValidationMessages
{
    public function categoryValidationMessages(): array
    {
        return [
            'name.required' => 'Naziv kategorije je obavezan.',
            'name.string' => 'Naziv kategorije mora biti tekst.',
            'name.max' => 'Naziv kategorije ne sme biti duži od 255 karaktera.',
            'type.required' => 'Tip kategorije je obavezan.',
            'type.enum' => 'Izabrani tip kategorije nije ispravan.',
            'icon.string' => 'Ikonica mora biti tekst.',
            'icon.max' => 'Ikonica ne sme biti duža od 50 karaktera.',
            'color.string' => 'Boja mora biti tekst.',
            'color.max' => 'Boja ne sme biti duža od 7 karaktera.',
        ];
    }
}
