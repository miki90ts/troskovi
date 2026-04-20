<?php

namespace App\Services;

use App\Enums\RecurringFrequency;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RecurringTransactionService
{
    public function __construct(private TransactionService $transactionService) {}

    public function list(User $user): Collection
    {
        return $user->recurringTransactions()
            ->with(['category', 'bankAccount', 'debt'])
            ->withCount([
                'transactions as linked_transactions_count' => fn($query) => $query->withTrashed(),
            ])
            ->orderByDesc('is_active')
            ->orderBy('next_due_date')
            ->get();
    }

    public function create(User $user, array $data): RecurringTransaction
    {
        $recurring = $user->recurringTransactions()->create(
            $this->prepareScheduleData($data)
        );

        return $this->hydrate($recurring);
    }

    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $recurring->update($this->prepareScheduleData($data, $recurring));

        return $this->hydrate($recurring);
    }

    public function delete(RecurringTransaction $recurring): void
    {
        if ($this->hasLinkedTransactions($recurring)) {
            throw ValidationException::withMessages([
                'recurring_transaction' => 'Recurring transaction with linked transactions cannot be deleted.',
            ]);
        }

        $recurring->delete();
    }

    public function processDue(): int
    {
        $due = RecurringTransaction::due()->with('user')->get();
        $count = 0;

        foreach ($due as $recurring) {
            while ($recurring->next_due_date <= now()->toDateString()) {
                $this->transactionService->create($recurring->user, [
                    'bank_account_id' => $recurring->bank_account_id,
                    'category_id' => $recurring->category_id,
                    'debt_id' => $recurring->debt_id,
                    'recurring_transaction_id' => $recurring->id,
                    'type' => $recurring->type,
                    'amount' => $recurring->amount,
                    'date' => $recurring->next_due_date,
                    'description' => $recurring->description,
                    'payment_method' => $recurring->payment_method,
                ]);

                $recurring->last_processed_date = $recurring->next_due_date;
                $recurring->next_due_date = $this->advanceDate(
                    $recurring->next_due_date,
                    $recurring->frequency
                );

                $count++;
            }

            $recurring->save();
        }

        return $count;
    }

    private function advanceDate(\DateTimeInterface $date, RecurringFrequency $frequency): CarbonImmutable
    {
        $carbon = CarbonImmutable::parse($date);

        return match ($frequency) {
            RecurringFrequency::Daily => $carbon->addDay(),
            RecurringFrequency::Weekly => $carbon->addWeek(),
            RecurringFrequency::Monthly => $carbon->addMonth(),
        };
    }

    public function hasLinkedTransactions(RecurringTransaction $recurring): bool
    {
        return $recurring->transactions()->withTrashed()->exists();
    }

    private function prepareScheduleData(array $data, ?RecurringTransaction $recurring = null): array
    {
        $scheduleTouched = array_key_exists('frequency', $data)
            || array_key_exists('next_due_date', $data);

        if (!$scheduleTouched) {
            return $data;
        }

        $frequency = RecurringFrequency::from(
            (string) ($data['frequency'] ?? $recurring?->frequency?->value)
        );
        $nextDueDate = CarbonImmutable::parse(
            $data['next_due_date'] ?? $recurring?->next_due_date
        );

        $data['frequency'] = $frequency->value;
        $data['next_due_date'] = $nextDueDate->toDateString();

        return $data;
    }

    private function hydrate(RecurringTransaction $recurring): RecurringTransaction
    {
        return $recurring->refresh()
            ->load(['category', 'bankAccount', 'debt'])
            ->loadCount([
                'transactions as linked_transactions_count' => fn($query) => $query->withTrashed(),
            ]);
    }
}
