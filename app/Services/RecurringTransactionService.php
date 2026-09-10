<?php

namespace App\Services;

use App\Enums\RecurringFrequency;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class RecurringTransactionService
{
    private const ALLOWED_PER_PAGE = [15, 30, 50, 100];

    public function __construct(
        private TransactionService $transactionService,
        private MoneyService $moneyService,
    ) {}

    public function list(User $user): Collection
    {
        return $this->query($user)
            ->orderByDesc('is_active')
            ->orderBy('next_due_date')
            ->get();
    }

    public function paginate(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->query($user);

        if (! empty($filters['type']) && in_array($filters['type'], ['expense', 'income'], true)) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('description', 'like', "%{$search}%");
        }

        $categoryIds = $this->extractCategoryIds($filters);

        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            match ($filters['status']) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        if (! empty($filters['date_from'])) {
            $query->where('next_due_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('next_due_date', '<=', $filters['date_to']);
        }

        return $query
            ->orderByDesc('is_active')
            ->orderBy('next_due_date')
            ->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    private function query(User $user): HasMany
    {
        return $user->recurringTransactions()
            ->with(['category', 'bankAccount', 'currency', 'debt'])
            ->withCount([
                'transactions as linked_transactions_count' => fn ($query) => $query->withTrashed(),
            ]);
    }

    public function create(User $user, array $data): RecurringTransaction
    {
        $recurring = $user->recurringTransactions()->create(
            $this->moneyService->applyRecurringSnapshot(
                $user,
                $this->prepareScheduleData($data),
            )
        );

        return $this->hydrate($recurring);
    }

    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $recurring->update(
            $this->moneyService->applyRecurringSnapshot(
                $recurring->user,
                $this->prepareScheduleData($data, $recurring),
                $recurring,
            )
        );

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
                    'currency_id' => $recurring->currency_id,
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

        if (! $scheduleTouched) {
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
            ->load(['category', 'bankAccount', 'currency', 'debt'])
            ->loadCount([
                'transactions as linked_transactions_count' => fn ($query) => $query->withTrashed(),
            ]);
    }

    private function resolvePerPage(mixed $perPage): int
    {
        $value = filter_var($perPage, FILTER_VALIDATE_INT);

        if (! is_int($value) || ! in_array($value, self::ALLOWED_PER_PAGE, true)) {
            return 15;
        }

        return $value;
    }

    private function extractCategoryIds(array $filters): array
    {
        $categoryIds = [];

        if (! empty($filters['category_ids']) && is_string($filters['category_ids'])) {
            $categoryIds = explode(',', $filters['category_ids']);
        } elseif (! empty($filters['category_id'])) {
            $categoryIds = [(string) $filters['category_id']];
        }

        return array_values(array_filter(
            array_map(static fn ($value) => trim((string) $value), $categoryIds),
            static fn ($value) => $value !== '',
        ));
    }
}
