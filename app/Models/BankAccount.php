<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'bank_name',
        'account_number',
        'currency',
        'color',
        'icon',
        'initial_balance',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'is_archived' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    // ── Accessors ──

    public function getMaskedAccountNumberAttribute(): string
    {
        if (! $this->account_number) {
            return '';
        }

        $last4 = substr($this->account_number, -4);

        return '••••'.$last4;
    }

    public function getCurrentBalanceAttribute(): string
    {
        $income = $this->transactions()
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $expense = $this->transactions()
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        return number_format((float) $this->initial_balance + $income - $expense, 2, '.', '');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }
}
