<?php

namespace App\Models;

use App\Enums\RecurringFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpendingTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'period',
        'target_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'period' => RecurringFrequency::class,
            'target_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isOverall(): bool
    {
        return $this->category_id === null;
    }
}
