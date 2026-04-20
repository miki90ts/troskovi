<?php

namespace App\Models;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'person_name',
        'description',
        'amount',
        'remaining_amount',
        'date',
        'due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => DebtType::class,
            'amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'date' => 'date',
            'due_date' => 'date',
            'status' => DebtStatus::class,
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

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', DebtStatus::Active);
    }

    public function scopeSettled($query)
    {
        return $query->where('status', DebtStatus::Settled);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', DebtStatus::Overdue);
    }

    public function scopeIOwe($query)
    {
        return $query->where('type', DebtType::IOwe);
    }

    public function scopeOwedToMe($query)
    {
        return $query->where('type', DebtType::OwedToMe);
    }

    // ── Accessors ──

    public function getPaidAmountAttribute(): float
    {
        return round((float) $this->amount - (float) $this->remaining_amount, 2);
    }

    public function getProgressPercentAttribute(): float
    {
        if ((float) $this->amount <= 0) {
            return 0;
        }

        return round(($this->paid_amount / (float) $this->amount) * 100, 1);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status === DebtStatus::Active;
    }
}
