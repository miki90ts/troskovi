<?php

namespace App\Services;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\TransactionType;
use App\Models\Debt;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DebtService
{
    public function list(User $user, array $filters = []): Collection
    {
        $query = $user->debts();

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
        $data['remaining_amount'] = $data['amount'];
        $data['status'] = DebtStatus::Active->value;

        $debt = $user->debts()->create($data);

        return $debt->loadCount('transactions');
    }

    public function update(Debt $debt, array $data): Debt
    {
        if (isset($data['amount']) && $data['amount'] != $debt->amount) {
            $paidSoFar = (float) $debt->amount - (float) $debt->remaining_amount;
            $newRemaining = max(0, (float) $data['amount'] - $paidSoFar);
            $data['remaining_amount'] = round($newRemaining, 2);

            if ($newRemaining <= 0) {
                $data['status'] = DebtStatus::Settled->value;
            } elseif (($data['status'] ?? $debt->status->value) === DebtStatus::Settled->value) {
                $data['status'] = DebtStatus::Active->value;
            }
        }

        $debt->update($data);

        return $debt->fresh()->loadCount('transactions');
    }

    public function delete(Debt $debt): void
    {
        $debt->transactions()->update(['debt_id' => null]);
        $debt->delete();
    }

    public function recalculateRemaining(Debt $debt): void
    {
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
            ->sum('amount');

        $increasingSum = (float) $debt->transactions()
            ->where('type', $increasingType)
            ->sum('amount');

        // amount stays as original base; remaining reflects increases and reductions
        $remaining = max(0, round((float) $debt->amount + $increasingSum - $reducingSum, 2));

        $status = $remaining <= 0
            ? DebtStatus::Settled
            : ($debt->due_date && $debt->due_date->isPast()
                ? DebtStatus::Overdue
                : DebtStatus::Active);

        $debt->update([
            'remaining_amount' => $remaining,
            'status' => $status,
        ]);
    }

    public function getSummary(User $user): array
    {
        $debts = $user->debts()->get();

        $activeDebts = $debts->where('status', DebtStatus::Active);
        $overdueDebts = $debts->where('status', DebtStatus::Overdue);
        $activeAndOverdue = $activeDebts->merge($overdueDebts);

        return [
            'total_i_owe' => round($activeAndOverdue->where('type', DebtType::IOwe)->sum('remaining_amount'), 2),
            'total_owed_to_me' => round($activeAndOverdue->where('type', DebtType::OwedToMe)->sum('remaining_amount'), 2),
            'active_count' => $activeDebts->count(),
            'overdue_count' => $overdueDebts->count(),
            'settled_count' => $debts->where('status', DebtStatus::Settled)->count(),
            'total_count' => $debts->count(),
        ];
    }
}
