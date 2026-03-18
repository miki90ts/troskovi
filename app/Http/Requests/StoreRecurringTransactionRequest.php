<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
            'frequency' => ['required', Rule::enum(RecurringFrequency::class)],
            'next_due_date' => ['required', 'date'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_method' => ['required_if:type,expense', Rule::enum(PaymentMethod::class)],
        ];
    }
}
