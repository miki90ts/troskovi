<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'frequency',
        'next_due_date',
        'last_processed_date',
        'payment_method',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'frequency' => RecurringFrequency::class,
            'next_due_date' => 'date',
            'last_processed_date' => 'date',
            'payment_method' => PaymentMethod::class,
            'is_active' => 'boolean',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->active()->where('next_due_date', '<=', now()->toDateString());
    }
}
