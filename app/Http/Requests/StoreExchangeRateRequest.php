<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')->where(
                    fn($query) => $query->where('active', true)->where('iso_code', '!=', 'RSD')
                ),
            ],
            'date' => ['required', 'date'],
            'rate' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency_id.required' => 'Valuta je obavezna.',
            'currency_id.integer' => 'Valuta mora biti broj.',
            'currency_id.exists' => 'Izabrana valuta nije ispravna.',
            'date.required' => 'Datum je obavezan.',
            'date.date' => 'Datum nije ispravan.',
            'rate.required' => 'Kurs je obavezan.',
            'rate.numeric' => 'Kurs mora biti broj.',
            'rate.gt' => 'Kurs mora biti veći od 0.',
        ];
    }
}
