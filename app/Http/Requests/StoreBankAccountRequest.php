<?php

namespace App\Http\Requests;

use App\Concerns\BankAccountValidationMessages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    use BankAccountValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'currency' => [
                'required_without:currency_id',
                'string',
                'max:10',
                Rule::exists('currencies', 'iso_code')->where('active', true),
            ],
            'currency_id' => [
                'required_without:currency',
                'integer',
                Rule::exists('currencies', 'id')->where('active', true),
            ],
            'color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('currency') && is_string($this->input('currency'))) {
            $this->merge([
                'currency' => strtoupper(trim($this->string('currency')->toString())),
            ]);
        }
    }

    public function messages(): array
    {
        return $this->bankAccountValidationMessages();
    }
}
