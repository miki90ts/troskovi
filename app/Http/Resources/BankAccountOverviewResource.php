<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use App\Http\Resources\BankAccountResource;
use Illuminate\Http\Request;

class BankAccountOverviewResource extends BankAccountResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $totalIncome = $this->transactions()
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $totalExpenses = $this->transactions()
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $lastTransaction = $this->transactions()
            ->latest('date')
            ->first();

        $data['total_income'] = round($totalIncome, 2);
        $data['total_expenses'] = round($totalExpenses, 2);
        $data['last_transaction_date'] = $lastTransaction?->date?->toDateString();

        return $data;
    }
}
