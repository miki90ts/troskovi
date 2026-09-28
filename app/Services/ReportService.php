<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(private MoneyService $moneyService) {}

    public function getSummary(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        [$prevStart, $prevEnd] = $this->getPreviousDateRange($period);

        return $this->buildSummary($user, $start, $end, $prevStart, $prevEnd);
    }

    public function getCurrentSummary(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getCurrentDateRange($period);
        [$prevStart, $prevEnd] = $this->getPreviousCurrentDateRange($period);

        return $this->buildSummary($user, $start, $end, $prevStart, $prevEnd);
    }

    public function getIncomeVsExpenses(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $groupFormat = $this->getGroupFormat($period);
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);

        $income = $this->moneyService->convertBaseSeriesToCurrency(
            $user->transactions()
                ->income()
                ->whereBetween('date', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(date, '{$groupFormat}') as bucket"),
                    DB::raw('date as rate_date'),
                    DB::raw('SUM(COALESCE(base_amount, amount)) as base_total')
                )
                ->groupBy('bucket', 'rate_date')
                ->orderBy('bucket')
                ->get(),
            $targetCurrency,
        );

        $expenses = $this->moneyService->convertBaseSeriesToCurrency(
            $user->transactions()
                ->expense()
                ->whereBetween('date', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(date, '{$groupFormat}') as bucket"),
                    DB::raw('date as rate_date'),
                    DB::raw('SUM(COALESCE(base_amount, amount)) as base_total')
                )
                ->groupBy('bucket', 'rate_date')
                ->orderBy('bucket')
                ->get(),
            $targetCurrency,
        );

        $periods = collect(array_keys($income))
            ->merge(array_keys($expenses))
            ->unique()
            ->sort()
            ->values();

        return [
            'labels' => $periods->toArray(),
            'income' => $periods->map(fn ($p) => round($income[$p] ?? 0, 2))->toArray(),
            'expenses' => $periods->map(fn ($p) => round($expenses[$p] ?? 0, 2))->toArray(),
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }

    public function getNetBalanceOverTime(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $groupFormat = $this->getGroupFormat($period);
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);

        $transactions = $user->transactions()
            ->whereBetween('date', [$start, $end])
            ->select(
                DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"),
                DB::raw('date as rate_date'),
                'type',
                DB::raw('SUM(COALESCE(base_amount, amount)) as total')
            )
            ->groupBy('period', 'rate_date', 'type')
            ->orderBy('period')
            ->get();

        $grouped = [];
        foreach ($transactions as $t) {
            $p = $t->period;
            if (! isset($grouped[$p])) {
                $grouped[$p] = ['income' => 0, 'expense' => 0];
            }
            $grouped[$p][$t->getRawOriginal('type')] += $this->moneyService->convertFromBase(
                (float) $t->total,
                $targetCurrency,
                $t->rate_date,
            );
        }

        ksort($grouped);

        $labels = [];
        $values = [];
        $cumulative = 0;

        foreach ($grouped as $p => $data) {
            $labels[] = $p;
            $cumulative += $data['income'] - $data['expense'];
            $values[] = round($cumulative, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }

    public function getExpenseBreakdown(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);

        $breakdown = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('date as rate_date'), DB::raw('SUM(COALESCE(base_amount, amount)) as total'))
            ->groupBy('category_id', 'rate_date')
            ->with('category')
            ->get();

        $totals = $this->aggregateBreakdown($breakdown, $targetCurrency);
        $sorted = collect($totals)->sortByDesc('total')->values();

        return [
            'labels' => $sorted->pluck('name')->toArray(),
            'values' => $sorted->pluck('total')->map(fn ($value) => round($value, 2))->toArray(),
            'colors' => $sorted->pluck('color')->toArray(),
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }

    public function getIncomeBreakdown(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);

        $breakdown = $user->transactions()
            ->income()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('date as rate_date'), DB::raw('SUM(COALESCE(base_amount, amount)) as total'))
            ->groupBy('category_id', 'rate_date')
            ->with('category')
            ->get();

        $totals = $this->aggregateBreakdown($breakdown, $targetCurrency);
        $sorted = collect($totals)->sortByDesc('total')->values();

        return [
            'labels' => $sorted->pluck('name')->toArray(),
            'values' => $sorted->pluck('total')->map(fn ($value) => round($value, 2))->toArray(),
            'colors' => $sorted->pluck('color')->toArray(),
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }

    public function getCashVsBank(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);

        $split = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->select('payment_method', DB::raw('date as rate_date'), DB::raw('SUM(COALESCE(base_amount, amount)) as total'))
            ->groupBy('payment_method', 'rate_date')
            ->get();

        $cash = 0;
        $bank = 0;

        foreach ($split as $row) {
            $converted = $this->moneyService->convertFromBase(
                (float) $row->total,
                $targetCurrency,
                $row->rate_date,
            );

            if ($row->payment_method === 'cash') {
                $cash += $converted;
            } else {
                $bank += $converted;
            }
        }

        return [
            'labels' => ['Keš', 'Bankovni račun'],
            'values' => [
                round($cash, 2),
                round($bank, 2),
            ],
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }

    private function buildSummary(
        User $user,
        CarbonImmutable $start,
        CarbonImmutable $end,
        CarbonImmutable $prevStart,
        CarbonImmutable $prevEnd,
    ): array {
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);

        $totalIncome = $this->sumConvertedTransactions($user, $start, $end, 'income', $targetCurrency);
        $totalExpenses = $this->sumConvertedTransactions($user, $start, $end, 'expense', $targetCurrency);
        $prevIncome = $this->sumConvertedTransactions($user, $prevStart, $prevEnd, 'income', $targetCurrency);
        $prevExpenses = $this->sumConvertedTransactions($user, $prevStart, $prevEnd, 'expense', $targetCurrency);

        $incomeChange = $prevIncome != 0
            ? round((($totalIncome - $prevIncome) / abs($prevIncome)) * 100, 1)
            : ($totalIncome > 0 ? 100 : 0);

        $netSavings = $totalIncome - $totalExpenses;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0;

        $expenseBreakdownRows = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('date as rate_date'), DB::raw('SUM(COALESCE(base_amount, amount)) as total'))
            ->groupBy('category_id', 'rate_date')
            ->with('category')
            ->get();

        $expenseBreakdown = collect($this->aggregateBreakdown($expenseBreakdownRows, $targetCurrency))
            ->sortByDesc('total')
            ->values();

        $prevNet = $prevIncome - $prevExpenses;
        $momChange = $prevNet != 0
            ? round((($netSavings - $prevNet) / abs($prevNet)) * 100, 1)
            : ($netSavings > 0 ? 100 : 0);

        return [
            'total_income' => round($totalIncome, 2),
            'total_expenses' => round($totalExpenses, 2),
            'income_change' => $incomeChange,
            'net_savings' => round($netSavings, 2),
            'savings_rate' => $savingsRate,
            'biggest_expense_category' => $expenseBreakdown->first()['name'] ?? 'N/A',
            'biggest_expense_amount' => round($expenseBreakdown->first()['total'] ?? 0, 2),
            'mom_change' => $momChange,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }

    private function sumConvertedTransactions(
        User $user,
        CarbonImmutable $start,
        CarbonImmutable $end,
        string $type,
        $targetCurrency,
    ): float {
        return round($user->transactions()
            ->where('type', $type)
            ->whereBetween('date', [$start, $end])
            ->select(DB::raw('date as rate_date'), DB::raw('SUM(COALESCE(base_amount, amount)) as base_total'))
            ->groupBy('rate_date')
            ->get()
            ->sum(fn ($row) => $this->moneyService->convertFromBase(
                (float) $row->base_total,
                $targetCurrency,
                $row->rate_date,
            )), 2);
    }

    private function aggregateBreakdown(Collection $rows, $targetCurrency): array
    {
        $totals = [];

        foreach ($rows as $row) {
            $categoryId = (string) $row->category_id;

            if (! isset($totals[$categoryId])) {
                $totals[$categoryId] = [
                    'name' => $row->category?->name ?? 'Uncategorized',
                    'color' => $row->category?->color ?? '#6b7280',
                    'total' => 0.0,
                ];
            }

            $totals[$categoryId]['total'] += $this->moneyService->convertFromBase(
                (float) $row->total,
                $targetCurrency,
                $row->rate_date,
            );
        }

        return $totals;
    }

    private function getDateRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'weekly' => [$now->subWeeks(12)->startOfWeek(), $now->endOfWeek()],
            'yearly' => [$now->subYears(5)->startOfYear(), $now->endOfYear()],
            default => [$now->subMonths(12)->startOfMonth(), $now->endOfMonth()],
        };
    }

    private function getCurrentDateRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'weekly' => [$now->startOfWeek(), $now->endOfWeek()],
            'yearly' => [$now->startOfYear(), $now->endOfYear()],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }

    private function getPreviousDateRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'weekly' => [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
            'yearly' => [$now->subYear()->startOfYear(), $now->subYear()->endOfYear()],
            default => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
        };
    }

    private function getPreviousCurrentDateRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'weekly' => [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
            'yearly' => [$now->subYear()->startOfYear(), $now->subYear()->endOfYear()],
            default => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
        };
    }

    private function getGroupFormat(string $period): string
    {
        return match ($period) {
            'weekly' => '%x-W%v',
            'yearly' => '%Y',
            default => '%Y-%m',
        };
    }
}
