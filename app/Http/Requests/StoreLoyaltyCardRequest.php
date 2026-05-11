<?php

namespace App\Http\Requests;

use App\Concerns\LoyaltyCardValidationMessages;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoyaltyCardRequest extends FormRequest
{
    use LoyaltyCardValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'max:7'],
        ];
    }

    public function messages(): array
    {
        return $this->loyaltyCardValidationMessages();
    }
}
