<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    private const ALLOWED_PER_PAGE = [15, 30, 50, 100];

    public function __construct(
        private DebtService $debtService,
        private MoneyService $moneyService,
    ) {}

    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->transactions()->with(['category', 'bankAccount', 'currency', 'debt']);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        $categoryIds = $this->extractCategoryIds($filters);

        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (! empty($filters['bank_account_id'])) {
            $query->where('bank_account_id', $filters['bank_account_id']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['is_warranty'])) {
            $query->where('is_warranty', filter_var($filters['is_warranty'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'active' => $query->where('warranty_expires_at', '>=', now()),
                'expiring_soon' => $query->where('warranty_expires_at', '>=', now())
                    ->where('warranty_expires_at', '<=', now()->addDays(30)),
                'expired' => $query->where('warranty_expires_at', '<', now()),
                default => null,
            };
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
        $perPage = $this->resolvePerPage($filters['per_page'] ?? null);

        return $query->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function create(User $user, array $data): Transaction
    {
        if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
            $data['receipt_path'] = $this->storeReceipt($user, $data['receipt']);
            unset($data['receipt']);
        }

        if (! empty($data['is_warranty']) && isset($data['date'])) {
            $data['warranty_expires_at'] = Carbon::parse($data['date'])->addYears(2)->toDateString();
        }

        $data = $this->moneyService->applyTransactionSnapshot($user, $data);

        $transaction = $user->transactions()->create($data);

        if ($transaction->debt_id) {
            $this->debtService->recalculateRemaining($transaction->debt);
        }

        return $transaction->load(['category', 'bankAccount', 'currency']);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $oldDebtId = $transaction->debt_id;

        if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
            if ($transaction->receipt_path) {
                Storage::disk('local')->delete($transaction->receipt_path);
            }
            $data['receipt_path'] = $this->storeReceipt($transaction->user, $data['receipt']);
            unset($data['receipt']);
        }

        if (isset($data['is_warranty'])) {
            if (! empty($data['is_warranty'])) {
                $date = $data['date'] ?? $transaction->date->toDateString();
                $data['warranty_expires_at'] = Carbon::parse($date)->addYears(2)->toDateString();
            } else {
                $data['warranty_expires_at'] = null;
            }
        }

        $data = $this->moneyService->applyTransactionSnapshot($transaction->user, $data, $transaction);

        $transaction->update($data);

        $newDebtId = $transaction->debt_id;

        if ($oldDebtId && $oldDebtId !== $newDebtId) {
            $oldDebt = Debt::find($oldDebtId);
            if ($oldDebt) {
                $this->debtService->recalculateRemaining($oldDebt);
            }
        }

        if ($newDebtId) {
            $this->debtService->recalculateRemaining($transaction->debt);
        }

        return $transaction->fresh(['category', 'bankAccount', 'currency']);
    }

    public function delete(Transaction $transaction): void
    {
        $debtId = $transaction->debt_id;

        if ($transaction->receipt_path) {
            Storage::disk('local')->delete($transaction->receipt_path);
        }

        $transaction->delete();

        if ($debtId) {
            $debt = Debt::find($debtId);
            if ($debt) {
                $this->debtService->recalculateRemaining($debt);
            }
        }
    }

    public function listAll(User $user, array $filters = [], int $limit = 5000): Collection
    {
        $query = $user->transactions()->with(['category', 'bankAccount', 'currency', 'debt']);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        $categoryIds = $this->extractCategoryIds($filters);

        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
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

        return $query->orderBy($sortBy, $sortDir)->limit($limit)->get();
    }

    public function getRecent(User $user, int $limit = 5): Collection
    {
        return $user->transactions()
            ->with(['category', 'bankAccount', 'currency'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function storeReceipt(User $user, UploadedFile $file): string
    {
        $path = $file->store("receipts/{$user->id}", 'local');

        $this->compressImage(Storage::disk('local')->path($path));

        return $path;
    }

    private function resolvePerPage(mixed $perPage): int
    {
        $value = filter_var($perPage, FILTER_VALIDATE_INT);

        if (! is_int($value) || ! in_array($value, self::ALLOWED_PER_PAGE, true)) {
            return 15;
        }

        return $value;
    }

    private function compressImage(string $absolutePath): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $mime = mime_content_type($absolutePath);

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => @imagecreatefromwebp($absolutePath),
            default => false,
        };

        if (! $image) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 1200;

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        imagejpeg($image, $absolutePath, 70);
        imagedestroy($image);
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
            array_map(static fn($value) => trim((string) $value), $categoryIds),
            static fn($value) => $value !== '',
        ));
    }
}
