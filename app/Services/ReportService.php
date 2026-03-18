<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSummary(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        [$prevStart, $prevEnd] = $this->getPreviousDateRange($period);

        $totalIncome = $user->transactions()
            ->income()
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        $totalExpenses = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        $prevIncome = $user->transactions()
            ->income()
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        $prevExpenses = $user->transactions()
            ->expense()
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        $netSavings = $totalIncome - $totalExpenses;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0;

        $biggestExpenseCategory = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        $prevNet = $prevIncome - $prevExpenses;
        $momChange = $prevNet != 0
            ? round((($netSavings - $prevNet) / abs($prevNet)) * 100, 1)
            : ($netSavings > 0 ? 100 : 0);

        return [
            'total_income' => round($totalIncome, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_savings' => round($netSavings, 2),
            'savings_rate' => $savingsRate,
            'biggest_expense_category' => $biggestExpenseCategory?->category?->name ?? 'N/A',
            'biggest_expense_amount' => round($biggestExpenseCategory?->total ?? 0, 2),
            'mom_change' => $momChange,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ];
    }

    public function getIncomeVsExpenses(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $groupFormat = $this->getGroupFormat($period);

        $income = $user->transactions()
            ->income()
            ->whereBetween('date', [$start, $end])
            ->select(DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"), DB::raw('SUM(amount) as total'))
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $expenses = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->select(DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"), DB::raw('SUM(amount) as total'))
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $periods = $income->keys()->merge($expenses->keys())->unique()->sort()->values();

        return [
            'labels' => $periods->toArray(),
            'income' => $periods->map(fn($p) => round($income->get($p, 0), 2))->toArray(),
            'expenses' => $periods->map(fn($p) => round($expenses->get($p, 0), 2))->toArray(),
        ];
    }

    public function getNetBalanceOverTime(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);
        $groupFormat = $this->getGroupFormat($period);

        $transactions = $user->transactions()
            ->whereBetween('date', [$start, $end])
            ->select(
                DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('period', 'type')
            ->orderBy('period')
            ->get();

        $grouped = [];
        foreach ($transactions as $t) {
            $p = $t->period;
            if (! isset($grouped[$p])) {
                $grouped[$p] = ['income' => 0, 'expense' => 0];
            }
            $grouped[$p][$t->getRawOriginal('type')] = $t->total;
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
        ];
    }

    public function getExpenseBreakdown(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $breakdown = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $breakdown->map(fn($b) => $b->category?->name ?? 'Uncategorized')->toArray(),
            'values' => $breakdown->map(fn($b) => round($b->total, 2))->toArray(),
            'colors' => $breakdown->map(fn($b) => $b->category?->color ?? '#6b7280')->toArray(),
        ];
    }

    public function getIncomeBreakdown(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $breakdown = $user->transactions()
            ->income()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $breakdown->map(fn($b) => $b->category?->name ?? 'Uncategorized')->toArray(),
            'values' => $breakdown->map(fn($b) => round($b->total, 2))->toArray(),
            'colors' => $breakdown->map(fn($b) => $b->category?->color ?? '#6b7280')->toArray(),
        ];
    }

    public function getCashVsBank(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $split = $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return [
            'labels' => ['Cash', 'Bank Account'],
            'values' => [
                round($split->get('cash', 0), 2),
                round($split->get('bank_account', 0), 2),
            ],
        ];
    }

    private function getDateRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'weekly' => [$now->subWeeks(12)->startOfWeek(), $now->endOfWeek()],
            'yearly' => [$now->subYears(5)->startOfYear(), $now->endOfYear()],
            default => [$now->subMonths(12)->startOfMonth(), $now->endOfMonth()], // monthly
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

    private function getGroupFormat(string $period): string
    {
        return match ($period) {
            'weekly' => '%x-W%v',
            'yearly' => '%Y',
            default => '%Y-%m',
        };
    }
}
