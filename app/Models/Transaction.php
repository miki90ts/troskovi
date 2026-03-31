<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'category_id',
        'recurring_transaction_id',
        'type',
        'amount',
        'date',
        'description',
        'notes',
        'payment_method',
        'receipt_path',
        'is_warranty',
        'warranty_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'date' => 'date',
            'payment_method' => PaymentMethod::class,
            'is_warranty' => 'boolean',
            'warranty_expires_at' => 'date',
        ];
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    // ── Scopes ──

    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense);
    }

    public function scopeForDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('date', '>=', $from);
        }

        if ($to) {
            $query->where('date', '<=', $to);
        }

        return $query;
    }

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('bank_account_id', $accountId);
    }

    public function scopeWarranty($query)
    {
        return $query->where('is_warranty', true);
    }

    public function getWarrantyIsExpiredAttribute(): bool
    {
        return $this->is_warranty
            && $this->warranty_expires_at
            && $this->warranty_expires_at->isPast();
    }
}
