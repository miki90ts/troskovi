<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(TransactionType::class)],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
            'date' => ['sometimes', 'date'],
            'description' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_method' => ['sometimes', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'max:1024'],
            'is_warranty' => ['nullable', 'boolean'],
        ];
    }
}
