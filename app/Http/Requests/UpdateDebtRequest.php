<?php

namespace App\Http\Requests;

use App\Enums\DebtType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(DebtType::class)],
            'person_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
            'date' => ['sometimes', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => ['sometimes', Rule::in(['active', 'settled'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
