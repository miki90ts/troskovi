<?php

namespace App\Services;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\TransactionType;
use App\Models\Debt;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class DebtService
{
    public function __construct(private MoneyService $moneyService) {}

    public function list(User $user, array $filters = []): Collection
    {
        $query = $user->debts()->with('currency');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('person_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->withCount('transactions')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'overdue' THEN 1 ELSE 2 END")
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(User $user, array $data): Debt
    {
        $data = $this->moneyService->applyDebtSnapshot($user, $data);
        $data['status'] = DebtStatus::Active->value;

        $debt = $user->debts()->create($data);

        return $debt->load(['currency'])->loadCount('transactions');
    }

    public function update(Debt $debt, array $data): Debt
    {
        $data = $this->moneyService->applyDebtSnapshot($debt->user, $data, $debt);

        if (isset($data['amount']) && $data['amount'] != $debt->amount) {
            $paidSoFar = (float) $debt->amount - (float) $debt->remaining_amount;
            $paidSoFarBase = (float) $debt->base_amount - (float) ($debt->remaining_base_amount ?? $debt->base_amount);
            $newRemaining = max(0, (float) $data['amount'] - $paidSoFar);
            $data['remaining_amount'] = round($newRemaining, 2);
            $data['remaining_base_amount'] = max(0, round((float) $data['base_amount'] - $paidSoFarBase, 2));

            if ($newRemaining <= 0) {
                $data['status'] = DebtStatus::Settled->value;
            } elseif (($data['status'] ?? $debt->status->value) === DebtStatus::Settled->value) {
                $data['status'] = DebtStatus::Active->value;
            }
        }

        $debt->update($data);

        return $debt->fresh(['currency'])->loadCount('transactions');
    }

    public function delete(Debt $debt): void
    {
        $debt->transactions()->update(['debt_id' => null]);
        $debt->delete();
    }

    public function recalculateRemaining(Debt $debt): void
    {
        $debtCurrency = $debt->currency ?? $this->moneyService->getBaseCurrency();

        // Transactions that REDUCE the debt (paying back / receiving back):
        //   - i_owe debt + expense transaction = I'm paying back what I owe
        //   - owed_to_me debt + income transaction = They're paying me back
        // Transactions that INCREASE the debt (borrowing more / lending more):
        //   - i_owe debt + income transaction = I'm borrowing more
        //   - owed_to_me debt + expense transaction = I'm lending them more

        $reducingType = $debt->type === DebtType::IOwe
            ? TransactionType::Expense
            : TransactionType::Income;

        $increasingType = $debt->type === DebtType::IOwe
            ? TransactionType::Income
            : TransactionType::Expense;

        $reducingSum = (float) $debt->transactions()
            ->where('type', $reducingType)
            ->get()
            ->sum(fn ($transaction) => $this->moneyService->convertFromBase(
                (float) ($transaction->base_amount ?? $transaction->amount),
                $debtCurrency,
                $transaction->date,
            ));

        $increasingSum = (float) $debt->transactions()
            ->where('type', $increasingType)
            ->get()
            ->sum(fn ($transaction) => $this->moneyService->convertFromBase(
                (float) ($transaction->base_amount ?? $transaction->amount),
                $debtCurrency,
                $transaction->date,
            ));

        $reducingBase = (float) $debt->transactions()
            ->where('type', $reducingType)
            ->get()
            ->sum(fn ($transaction) => (float) ($transaction->base_amount ?? $transaction->amount));

        $increasingBase = (float) $debt->transactions()
            ->where('type', $increasingType)
            ->get()
            ->sum(fn ($transaction) => (float) ($transaction->base_amount ?? $transaction->amount));

        $remaining = max(0, round((float) $debt->amount + $increasingSum - $reducingSum, 2));
        $remainingBase = max(0, round((float) $debt->base_amount + $increasingBase - $reducingBase, 2));

        $status = $remaining <= 0
            ? DebtStatus::Settled
            : ($debt->due_date && $debt->due_date->isPast()
                ? DebtStatus::Overdue
                : DebtStatus::Active);

        $debt->update([
            'remaining_amount' => $remaining,
            'remaining_base_amount' => $remainingBase,
            'status' => $status,
        ]);
    }

    public function getSummary(User $user): array
    {
        $targetCurrency = $this->moneyService->resolveUserCurrency($user);
        $conversionDate = CarbonImmutable::now()->toDateString();
        $debts = $user->debts()->with('currency')->get();

        $activeDebts = $debts->where('status', DebtStatus::Active);
        $overdueDebts = $debts->where('status', DebtStatus::Overdue);
        $activeAndOverdue = $activeDebts->merge($overdueDebts);

        return [
            'total_i_owe' => round($activeAndOverdue
                ->where('type', DebtType::IOwe)
                ->sum(fn (Debt $debt) => $this->moneyService->convertFromBase(
                    (float) ($debt->remaining_base_amount ?? $debt->base_amount ?? $debt->remaining_amount),
                    $targetCurrency,
                    $conversionDate,
                )), 2),
            'total_owed_to_me' => round($activeAndOverdue
                ->where('type', DebtType::OwedToMe)
                ->sum(fn (Debt $debt) => $this->moneyService->convertFromBase(
                    (float) ($debt->remaining_base_amount ?? $debt->base_amount ?? $debt->remaining_amount),
                    $targetCurrency,
                    $conversionDate,
                )), 2),
            'active_count' => $activeDebts->count(),
            'overdue_count' => $overdueDebts->count(),
            'settled_count' => $debts->where('status', DebtStatus::Settled)->count(),
            'total_count' => $debts->count(),
            'currency_code' => $targetCurrency->iso_code,
            'currency_symbol' => $targetCurrency->symbol,
        ];
    }
}
