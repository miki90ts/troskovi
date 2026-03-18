<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'bank_name' => ['sometimes', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
            'initial_balance' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
