<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->transactions()->with(['category', 'bankAccount']);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['bank_account_id'])) {
            $query->where('bank_account_id', $filters['bank_account_id']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'date';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $query->orderBy($sortBy, $sortDir)
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(User $user, array $data): Transaction
    {
        if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
            $data['receipt_path'] = $this->storeReceipt($user, $data['receipt']);
            unset($data['receipt']);
        }

        $transaction = $user->transactions()->create($data);

        return $transaction->load(['category', 'bankAccount']);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
            if ($transaction->receipt_path) {
                Storage::disk('local')->delete($transaction->receipt_path);
            }
            $data['receipt_path'] = $this->storeReceipt($transaction->user, $data['receipt']);
            unset($data['receipt']);
        }

        $transaction->update($data);

        return $transaction->fresh(['category', 'bankAccount']);
    }

    public function delete(Transaction $transaction): void
    {
        if ($transaction->receipt_path) {
            Storage::disk('local')->delete($transaction->receipt_path);
        }

        $transaction->delete();
    }

    public function getRecent(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $user->transactions()
            ->with(['category', 'bankAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function storeReceipt(User $user, UploadedFile $file): string
    {
        return $file->store("receipts/{$user->id}", 'local');
    }
}
