<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportTransactionsPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category_ids' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $categoryIds = array_values(array_filter(
                        array_map('trim', explode(',', $value)),
                        static fn ($item) => $item !== '',
                    ));

                    if ($categoryIds === [] || count(array_filter($categoryIds, 'ctype_digit')) !== count($categoryIds)) {
                        $fail('Izabrane kategorije nisu ispravne.');

                        return;
                    }

                    $existingCount = Category::query()
                        ->whereIn('id', $categoryIds)
                        ->count();

                    if ($existingCount !== count(array_unique($categoryIds))) {
                        $fail('Jedna ili više kategorija ne postoje.');
                    }
                },
            ],
            'bank_account_id' => ['nullable', 'integer', 'exists:bank_accounts,id'],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
