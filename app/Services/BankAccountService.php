<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BankAccountService
{
    public function list(User $user, bool $includeArchived = false): Collection
    {
        $query = $user->bankAccounts();

        if (! $includeArchived) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    public function create(User $user, array $data): BankAccount
    {
        return $user->bankAccounts()->create($data);
    }

    public function update(BankAccount $account, array $data): BankAccount
    {
        $account->update($data);

        return $account->fresh();
    }

    public function archive(BankAccount $account): BankAccount
    {
        $account->update(['is_archived' => true]);

        return $account;
    }

    public function restore(BankAccount $account): BankAccount
    {
        $account->update(['is_archived' => false]);

        return $account;
    }

    public function getOverview(BankAccount $account): array
    {
        $totalIncome = $account->transactions()
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $totalExpenses = $account->transactions()
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $lastTransaction = $account->transactions()
            ->latest('date')
            ->first();

        return [
            'current_balance' => $account->current_balance,
            'total_income' => number_format($totalIncome, 2, '.', ''),
            'total_expenses' => number_format($totalExpenses, 2, '.', ''),
            'last_transaction_date' => $lastTransaction?->date?->toDateString(),
        ];
    }

    public function transfer(User $user, array $data): AccountTransfer
    {
        return DB::transaction(function () use ($user, $data) {
            $fromTransaction = Transaction::create([
                'user_id' => $user->id,
                'bank_account_id' => $data['from_account_id'],
                'type' => TransactionType::Expense,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Account Transfer',
                'payment_method' => 'bank_account',
            ]);

            $toTransaction = Transaction::create([
                'user_id' => $user->id,
                'bank_account_id' => $data['to_account_id'],
                'type' => TransactionType::Income,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Account Transfer',
                'payment_method' => 'bank_account',
            ]);

            return AccountTransfer::create([
                'user_id' => $user->id,
                'from_account_id' => $data['from_account_id'],
                'to_account_id' => $data['to_account_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'date' => $data['date'],
                'from_transaction_id' => $fromTransaction->id,
                'to_transaction_id' => $toTransaction->id,
            ]);
        });
    }
}
