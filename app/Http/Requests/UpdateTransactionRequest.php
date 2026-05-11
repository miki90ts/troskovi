<?php

namespace App\Http\Requests;

use App\Concerns\TransactionValidationMessages;
use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    use TransactionValidationMessages;

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
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where(function ($nested) {
                        $nested->where('user_id', $this->user()?->id)
                            ->orWhere('is_system', true);
                    });
                }),
            ],
            'bank_account_id' => [
                'nullable',
                'integer',
                Rule::exists('bank_accounts', 'id')->where(
                    fn($query) => $query->where('user_id', $this->user()?->id)
                ),
            ],
            'payment_method' => ['sometimes', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'max:1024'],
            'is_warranty' => ['nullable', 'boolean'],
            'debt_id' => [
                'nullable',
                Rule::exists('debts', 'id')->where(
                    fn($query) => $query->where('user_id', $this->user()?->id)
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return $this->transactionValidationMessages();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $transaction = $this->route('transaction');
            $paymentMethod = $this->input('payment_method');

            if ($paymentMethod === null && $transaction instanceof Transaction) {
                $paymentMethod = $transaction->payment_method?->value;
            }

            $bankAccountId = $this->has('bank_account_id')
                ? $this->input('bank_account_id')
                : ($transaction instanceof Transaction ? $transaction->bank_account_id : null);

            $hasBankAccount = $bankAccountId !== null && $bankAccountId !== '';

            if ($paymentMethod === PaymentMethod::BankAccount->value && ! $hasBankAccount) {
                $validator->errors()->add(
                    'bank_account_id',
                    'Bankovni racun je obavezan kada je nacin placanja bankovni racun.'
                );
            }

            if ($paymentMethod !== null && $paymentMethod !== PaymentMethod::BankAccount->value && $hasBankAccount) {
                $validator->errors()->add(
                    'bank_account_id',
                    'Bankovni racun moze biti postavljen samo kada je nacin placanja bankovni racun.'
                );
            }
        });
    }
}
