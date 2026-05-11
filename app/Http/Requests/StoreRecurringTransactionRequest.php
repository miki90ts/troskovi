<?php

namespace App\Http\Requests;

use App\Concerns\RecurringTransactionValidationMessages;
use App\Enums\PaymentMethod;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
{
    use RecurringTransactionValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes'];

        return [
            'type' => [...$required, Rule::enum(TransactionType::class)],
            'amount' => [...$required, 'numeric', 'gt:0'],
            'description' => [...$required, 'string', 'max:255'],
            'frequency' => [...$required, Rule::enum(RecurringFrequency::class)],
            'next_due_date' => [...$required, 'date'],
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
            'debt_id' => [
                'nullable',
                Rule::exists('debts', 'id')->where(
                    fn($query) => $query->where('user_id', $this->user()?->id)
                ),
            ],
            'bank_account_id' => [
                'nullable',
                'integer',
                Rule::exists('bank_accounts', 'id')->where(
                    fn($query) => $query->where('user_id', $this->user()?->id)
                ),
            ],
            'payment_method' => ['required_if:type,expense', Rule::enum(PaymentMethod::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return $this->recurringTransactionValidationMessages();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $recurringTransaction = $this->route('recurring_transaction') ?? $this->route('recurringTransaction');
            $paymentMethod = $this->input('payment_method');

            if ($paymentMethod === null && $recurringTransaction instanceof RecurringTransaction) {
                $paymentMethod = $recurringTransaction->payment_method?->value;
            }

            $bankAccountId = $this->has('bank_account_id')
                ? $this->input('bank_account_id')
                : ($recurringTransaction instanceof RecurringTransaction ? $recurringTransaction->bank_account_id : null);

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
