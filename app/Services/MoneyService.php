<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Debt;
use App\Models\RecurringTransaction;
use App\Models\SpendingTarget;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MoneyService
{
    public const BASE_CURRENCY_ISO = 'RSD';

    public function getBaseCurrency(): Currency
    {
        return Currency::query()
            ->where('iso_code', self::BASE_CURRENCY_ISO)
            ->firstOrFail();
    }

    public function resolveUserCurrency(User $user): Currency
    {
        if ($user->relationLoaded('defaultCurrency') && $user->defaultCurrency) {
            return $user->defaultCurrency;
        }

        if ($user->default_currency_id) {
            $currency = Currency::find($user->default_currency_id);

            if ($currency) {
                return $currency;
            }
        }

        return $this->getBaseCurrency();
    }

    public function resolveBankAccountCurrency(BankAccount $account): Currency
    {
        if ($account->relationLoaded('currencyRef') && $account->currencyRef) {
            return $account->currencyRef;
        }

        if ($account->currency_id) {
            $currency = Currency::find($account->currency_id);

            if ($currency) {
                return $currency;
            }
        }

        return $this->getBaseCurrency();
    }

    public function resolveRate(Currency $currency, CarbonInterface|string $date): string
    {
        if ($currency->iso_code === self::BASE_CURRENCY_ISO) {
            return '1.000000';
        }

        $dateString = $date instanceof CarbonInterface
            ? $date->toDateString()
            : (string) $date;
        $cacheVersion = $this->rateCacheVersion($currency->id);

        $rate = Cache::remember(
            "exchange-rate:{$currency->id}:{$cacheVersion}:{$dateString}",
            now()->addMinutes(10),
            fn() => $currency->exchangeRates()
                ->where('date', '<=', $dateString)
                ->orderByDesc('date')
                ->value('rate')
        );

        if ($rate === null) {
            throw ValidationException::withMessages([
                'date' => "Kurs za {$currency->iso_code} na datum {$dateString} nije pronadjen.",
            ]);
        }

        return number_format((float) $rate, 6, '.', '');
    }

    public function applyTransactionSnapshot(User $user, array $data, ?Transaction $existing = null): array
    {
        if ($existing && ! $this->shouldRefreshSnapshot($data)) {
            return $data;
        }

        $amount = (float) ($data['amount'] ?? $existing?->amount ?? 0);
        $date = (string) ($data['date'] ?? $existing?->date?->toDateString() ?? now()->toDateString());
        $bankAccountId = array_key_exists('bank_account_id', $data)
            ? $data['bank_account_id']
            : $existing?->bank_account_id;
        $existingCurrency = $existing?->relationLoaded('currency') ? $existing->currency : null;

        $currency = $this->resolveTransactionCurrency($user, $bankAccountId, $data, $existingCurrency);
        $exchangeRate = $this->resolveRate($currency, $date);

        $data['currency_id'] = $currency->id;
        $data['exchange_rate'] = $exchangeRate;
        $data['base_amount'] = $this->convertToBase($amount, $exchangeRate);

        return $data;
    }

    public function applyRecurringSnapshot(User $user, array $data, ?RecurringTransaction $existing = null): array
    {
        if ($existing && ! $this->shouldRefreshRecurringCurrency($data)) {
            return $data;
        }

        $bankAccountId = array_key_exists('bank_account_id', $data)
            ? $data['bank_account_id']
            : $existing?->bank_account_id;
        $existingCurrency = $existing?->relationLoaded('currency') ? $existing->currency : null;

        $currency = $this->resolveTransactionCurrency($user, $bankAccountId, $data, $existingCurrency);
        $data['currency_id'] = $currency->id;

        return $data;
    }

    public function applyDebtSnapshot(User $user, array $data, ?Debt $existing = null): array
    {
        if ($existing && ! $this->shouldRefreshDebtSnapshot($data)) {
            return $data;
        }

        $amount = (float) ($data['amount'] ?? $existing?->amount ?? 0);
        $date = (string) ($data['date'] ?? $existing?->date?->toDateString() ?? now()->toDateString());
        $currency = $this->resolveExplicitCurrency($user, $data, $existing?->currency);
        $exchangeRate = $this->resolveRate($currency, $date);
        $baseAmount = $this->convertToBase($amount, $exchangeRate);

        $data['currency_id'] = $currency->id;
        $data['exchange_rate'] = $exchangeRate;
        $data['base_amount'] = $baseAmount;

        if (! $existing) {
            $data['remaining_amount'] = number_format($amount, 2, '.', '');
            $data['remaining_base_amount'] = $baseAmount;
        }

        return $data;
    }

    public function applySpendingTargetSnapshot(User $user, array $data, ?SpendingTarget $existing = null): array
    {
        if ($existing && ! array_key_exists('currency_id', $data) && ! array_key_exists('currency', $data)) {
            return $data;
        }

        $currency = $this->resolveExplicitCurrency($user, $data, $existing?->currency);
        $data['currency_id'] = $currency->id;

        return $data;
    }

    public function convertToBase(float $amount, float|string $exchangeRate): string
    {
        return number_format(round($amount * (float) $exchangeRate, 2), 2, '.', '');
    }

    public function convertFromBase(float $baseAmount, Currency $currency, CarbonInterface|string $date): float
    {
        if ($currency->iso_code === self::BASE_CURRENCY_ISO) {
            return round($baseAmount, 2);
        }

        $rate = (float) $this->resolveRate($currency, $date);

        return round($baseAmount / $rate, 2);
    }

    public function convertTransactionToCurrency(Transaction $transaction, Currency $currency): float
    {
        return $this->convertFromBase(
            (float) ($transaction->base_amount ?? 0),
            $currency,
            $transaction->date,
        );
    }

    public function convertBaseSeriesToCurrency(iterable $rows, Currency $currency): array
    {
        $totals = [];

        foreach ($rows as $row) {
            $bucket = $row->bucket;
            $totals[$bucket] = ($totals[$bucket] ?? 0)
                + $this->convertFromBase((float) $row->base_total, $currency, $row->rate_date);
        }

        return $totals;
    }

    public function activeCurrencies()
    {
        return Currency::query()
            ->where('active', true)
            ->orderBy('iso_code')
            ->get(['id', 'iso_code', 'name', 'symbol']);
    }

    public function resolveExplicitCurrencyForInput(array $data): ?Currency
    {
        if (! empty($data['currency_id'])) {
            return Currency::query()
                ->whereKey($data['currency_id'])
                ->where('active', true)
                ->first();
        }

        if (! empty($data['currency'])) {
            return Currency::query()
                ->where('iso_code', strtoupper((string) $data['currency']))
                ->where('active', true)
                ->first();
        }

        return null;
    }

    public function bumpRateCacheVersion(int $currencyId): void
    {
        $key = $this->rateCacheVersionKey($currencyId);
        $current = (int) Cache::get($key, 1);

        Cache::forever($key, $current + 1);
    }

    private function resolveTransactionCurrency(
        User $user,
        mixed $bankAccountId,
        array $data = [],
        ?Currency $fallback = null,
    ): Currency {
        if ($bankAccountId !== null && $bankAccountId !== '') {
            $account = $user->bankAccounts()
                ->with('currencyRef')
                ->find($bankAccountId);

            if (! $account) {
                throw ValidationException::withMessages([
                    'bank_account_id' => 'Bankovni racun nije dostupan za odredjivanje valute.',
                ]);
            }

            return $this->resolveBankAccountCurrency($account);
        }

        return $this->resolveExplicitCurrency($user, $data, $fallback);
    }

    private function shouldRefreshSnapshot(array $data): bool
    {
        return array_key_exists('amount', $data)
            || array_key_exists('date', $data)
            || array_key_exists('bank_account_id', $data)
            || array_key_exists('payment_method', $data)
            || array_key_exists('currency_id', $data)
            || array_key_exists('currency', $data);
    }

    private function resolveExplicitCurrency(User $user, array $data, ?Currency $fallback = null): Currency
    {
        $currency = $this->resolveExplicitCurrencyForInput($data);

        if ($currency) {
            return $currency;
        }

        if ($fallback) {
            return $fallback;
        }

        return $this->resolveUserCurrency($user);
    }

    private function shouldRefreshRecurringCurrency(array $data): bool
    {
        return array_key_exists('bank_account_id', $data)
            || array_key_exists('payment_method', $data)
            || array_key_exists('currency_id', $data)
            || array_key_exists('currency', $data);
    }

    private function shouldRefreshDebtSnapshot(array $data): bool
    {
        return array_key_exists('amount', $data)
            || array_key_exists('date', $data)
            || array_key_exists('currency_id', $data)
            || array_key_exists('currency', $data);
    }

    private function rateCacheVersion(int $currencyId): int
    {
        return (int) Cache::get($this->rateCacheVersionKey($currencyId), 1);
    }

    private function rateCacheVersionKey(int $currencyId): string
    {
        return "exchange-rate-version:{$currencyId}";
    }
}
