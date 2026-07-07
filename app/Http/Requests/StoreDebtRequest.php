<?php

namespace App\Http\Requests;

use App\Concerns\DebtValidationMessages;
use App\Enums\DebtType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDebtRequest extends FormRequest
{
    use DebtValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(DebtType::class)],
            'person_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_id' => ['nullable', 'integer', Rule::exists('currencies', 'id')->where('active', true)],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return $this->debtValidationMessages();
    }
}
