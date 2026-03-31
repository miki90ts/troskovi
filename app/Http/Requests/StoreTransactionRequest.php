<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_method' => ['required_if:type,expense', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'max:1024'],
            'is_warranty' => ['nullable', 'boolean'],
        ];
    }
}
