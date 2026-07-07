<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ExchangeRateService
{
    public function __construct(private MoneyService $moneyService) {}

    public function list(): Collection
    {
        return ExchangeRate::query()
            ->with('currency')
            ->orderByDesc('date')
            ->orderBy('currency_id')
            ->get();
    }

    public function upsert(array $data): ExchangeRate
    {
        $date = Carbon::parse($data['date'])->toDateString();

        $exchangeRate = ExchangeRate::query()
            ->where('currency_id', $data['currency_id'])
            ->whereDate('date', $date)
            ->first();

        if ($exchangeRate) {
            $exchangeRate->update(['rate' => $data['rate']]);
        } else {
            $exchangeRate = ExchangeRate::query()->create([
                'currency_id' => $data['currency_id'],
                'date' => $date,
                'rate' => $data['rate'],
            ]);
        }

        $this->moneyService->bumpRateCacheVersion((int) $exchangeRate->currency_id);

        return $exchangeRate->load('currency');
    }

    public function delete(ExchangeRate $exchangeRate): void
    {
        $currencyId = (int) $exchangeRate->currency_id;

        $exchangeRate->delete();
        $this->moneyService->bumpRateCacheVersion($currencyId);
    }
}
