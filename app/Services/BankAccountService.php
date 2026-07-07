<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class BankAccountService
{
    public function __construct(private MoneyService $moneyService) {}

    public function list(User $user, bool $includeArchived = false): Collection
    {
        $query = $user->bankAccounts()->with('currencyRef');

        if (! $includeArchived) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    public function listTransfers(User $user): Collection
    {
        return $user->accountTransfers()
            ->with([
                'fromAccount:id,name,currency,currency_id',
                'toAccount:id,name,currency,currency_id',
                'fromCurrency:id,iso_code,name,symbol',
                'toCurrency:id,iso_code,name,symbol',
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(User $user, array $data): BankAccount
    {
        return $user->bankAccounts()
            ->create($this->normalizeCurrencyPayload($data))
            ->load('currencyRef');
    }

    public function update(BankAccount $account, array $data): BankAccount
    {
        $account->update($this->normalizeCurrencyPayload($data, $account));

        return $account->fresh('currencyRef');
    }

    public function archive(BankAccount $account): BankAccount
    {
        $account->update(['is_archived' => true]);

        return $account;
    }

    public function restore(BankAccount $account): BankAccount
    {
        $account->update(['is_archived' => false]);

        return $account->fresh('currencyRef');
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
        $fromAccount = $user->bankAccounts()
            ->with('currencyRef')
            ->findOrFail($data['from_account_id']);
        $toAccount = $user->bankAccounts()
            ->with('currencyRef')
            ->findOrFail($data['to_account_id']);

        $fromCurrency = $this->moneyService->resolveBankAccountCurrency($fromAccount);
        $toCurrency = $this->moneyService->resolveBankAccountCurrency($toAccount);
        $amount = (float) $data['amount'];
        $date = (string) $data['date'];

        if ($fromCurrency->is($toCurrency)) {
            $exchangeRate = '1.000000';
            $baseAmount = $this->moneyService->convertToBase(
                $amount,
                $this->moneyService->resolveRate($fromCurrency, $date),
            );
            $toAmount = number_format($amount, 2, '.', '');
        } else {
            $sourceRate = $this->moneyService->resolveRate($fromCurrency, $date);
            $baseAmount = $this->moneyService->convertToBase($amount, $sourceRate);
            $toAmount = number_format(
                $this->moneyService->convertFromBase((float) $baseAmount, $toCurrency, $date),
                2,
                '.',
                '',
            );
            $exchangeRate = $sourceRate;
        }

        return AccountTransfer::create([
            'user_id' => $user->id,
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => $amount,
            'from_currency_id' => $fromCurrency->id,
            'to_currency_id' => $toCurrency->id,
            'to_amount' => $toAmount,
            'exchange_rate' => $exchangeRate,
            'base_amount' => $baseAmount,
            'description' => $data['description'] ?? null,
            'date' => $date,
        ])->load([
            'fromAccount:id,name,currency,currency_id',
            'toAccount:id,name,currency,currency_id',
            'fromCurrency:id,iso_code,name,symbol',
            'toCurrency:id,iso_code,name,symbol',
        ]);
    }

    private function normalizeCurrencyPayload(array $data, ?BankAccount $account = null): array
    {
        if ($account && ! array_key_exists('currency', $data) && ! array_key_exists('currency_id', $data)) {
            return $data;
        }

        $currency = $this->moneyService->resolveExplicitCurrencyForInput($data);

        if (! $currency) {
            throw ValidationException::withMessages([
                'currency_id' => 'Izabrana valuta nije ispravna.',
            ]);
        }

        $data['currency_id'] = $currency->id;
        $data['currency'] = $currency->iso_code;

        return $data;
    }
}
