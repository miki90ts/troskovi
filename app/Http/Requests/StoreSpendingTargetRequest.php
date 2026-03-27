<?php

namespace App\Http\Requests;

use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\SpendingTarget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpendingTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['required', Rule::enum(RecurringFrequency::class)],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('type', TransactionType::Expense->value)
                        ->where(function ($nested) {
                            $nested->where('user_id', $this->user()->id)
                                ->orWhere('is_system', true);
                        });
                }),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $query = SpendingTarget::query()
                ->where('user_id', $this->user()->id)
                ->where('period', $this->input('period'));

            if ($this->filled('category_id')) {
                $query->where('category_id', $this->integer('category_id'));
            } else {
                $query->whereNull('category_id');
            }

            $current = $this->route('spending_target') ?? $this->route('spendingTarget');

            if ($current instanceof SpendingTarget) {
                $query->whereKeyNot($current->id);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    'category_id',
                    'Budzet za izabrani opseg i period vec postoji.'
                );
            }
        });
    }
}
