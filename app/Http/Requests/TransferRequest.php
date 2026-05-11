<?php

namespace App\Http\Requests;

use App\Concerns\TransferValidationMessages;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransferRequest extends FormRequest
{
    use TransferValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'from_account_id' => [
                'required',
                Rule::exists('bank_accounts', 'id')->where(
                    fn(Builder $query) => $query
                        ->where('user_id', $userId)
                        ->where('is_archived', false)
                        ->whereNull('deleted_at'),
                ),
            ],
            'to_account_id' => [
                'required',
                Rule::exists('bank_accounts', 'id')->where(
                    fn(Builder $query) => $query
                        ->where('user_id', $userId)
                        ->where('is_archived', false)
                        ->whereNull('deleted_at'),
                ),
                'different:from_account_id',
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return $this->transferValidationMessages();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $fromAccount = $this->user()?->bankAccounts()->find(
                $this->integer('from_account_id'),
            );

            if (! $fromAccount) {
                return;
            }

            if ((float) $this->input('amount') > (float) $fromAccount->current_balance) {
                $validator->errors()->add(
                    'amount',
                    'Nema dovoljno sredstava na izvornom računu.',
                );
            }
        });
    }
}
