<?php

namespace App\Services;

use App\Enums\RecurringFrequency;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RecurringTransactionService
{
    public function list(User $user): Collection
    {
        return $user->recurringTransactions()
            ->with(['category', 'bankAccount'])
            ->orderBy('next_due_date')
            ->get();
    }

    public function create(User $user, array $data): RecurringTransaction
    {
        return $user->recurringTransactions()->create($data);
    }

    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $recurring->update($data);

        return $recurring->fresh(['category', 'bankAccount']);
    }

    public function delete(RecurringTransaction $recurring): void
    {
        $recurring->update(['is_active' => false]);
    }

    public function processDue(): int
    {
        $due = RecurringTransaction::due()->with('user')->get();
        $count = 0;

        foreach ($due as $recurring) {
            while ($recurring->next_due_date <= now()->toDateString()) {
                Transaction::create([
                    'user_id' => $recurring->user_id,
                    'bank_account_id' => $recurring->bank_account_id,
                    'category_id' => $recurring->category_id,
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

    private function advanceDate(\DateTimeInterface $date, RecurringFrequency $frequency): \DateTimeInterface
    {
        $carbon = \Carbon\CarbonImmutable::parse($date);

        return match ($frequency) {
            RecurringFrequency::Daily => $carbon->addDay(),
            RecurringFrequency::Weekly => $carbon->addWeek(),
            RecurringFrequency::Monthly => $carbon->addMonth(),
        };
    }
}
