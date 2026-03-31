<?php

namespace App\Services;

use App\Enums\RecurringFrequency;
use App\Models\SpendingTarget;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class SpendingTargetService
{
    private const WARNING_THRESHOLD_PERCENT = 80;

    public function list(User $user): Collection
    {
        return $user->spendingTargets()
            ->with('category')
            ->orderByDesc('is_active')
            ->orderByRaw('category_id is null desc')
            ->orderBy('period')
            ->get();
    }

    public function create(User $user, array $data): SpendingTarget
    {
        $target = $user->spendingTargets()->create([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $target->load('category');
    }

    public function update(SpendingTarget $spendingTarget, array $data): SpendingTarget
    {
        $spendingTarget->update($data);

        return $spendingTarget->fresh('category');
    }

    public function delete(SpendingTarget $spendingTarget): void
    {
        $spendingTarget->delete();
    }

    public function getProgress(User $user, string $period): array
    {
        $frequency = RecurringFrequency::tryFrom($period) ?? RecurringFrequency::Monthly;
        [$start, $end] = $this->getCurrentPeriodRange($frequency);

        $targets = $user->spendingTargets()
            ->active()
            ->with('category')
            ->where('period', $frequency)
            ->get()
            ->map(fn (SpendingTarget $target) => $this->buildProgressItem($user, $target, $start, $end));

        $overallTarget = $targets->firstWhere('scope', 'overall');

        $topRiskTarget = $targets
            ->sortByDesc(fn (array $target) => [$target['status_rank'], $target['progress_percent']])
            ->first();

        return [
            'period' => $frequency->value,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'warning_threshold_percent' => self::WARNING_THRESHOLD_PERCENT,
            'overall_target' => $overallTarget,
            'top_risk_target' => $topRiskTarget,
            'targets' => $targets->values()->all(),
            'summary' => [
                'active_count' => $targets->count(),
                'warning_count' => $targets->where('status', 'warning')->count(),
                'exceeded_count' => $targets->where('status', 'exceeded')->count(),
            ],
        ];
    }

    private function buildProgressItem(
        User $user,
        SpendingTarget $target,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): array {
        $spentAmount = (float) $user->transactions()
            ->expense()
            ->whereBetween('date', [$start, $end])
            ->when(
                $target->category_id,
                fn ($query) => $query->where('category_id', $target->category_id)
            )
            ->sum('amount');

        $targetAmount = (float) $target->target_amount;
        $progressPercent = $targetAmount > 0
            ? round(($spentAmount / $targetAmount) * 100, 1)
            : 0.0;
        $remainingAmount = round($targetAmount - $spentAmount, 2);
        $status = match (true) {
            $progressPercent >= 100 => 'exceeded',
            $progressPercent >= self::WARNING_THRESHOLD_PERCENT => 'warning',
            default => 'ok',
        };

        return [
            'id' => $target->id,
            'scope' => $target->isOverall() ? 'overall' : 'category',
            'period' => $target->period->value,
            'target_amount' => round($targetAmount, 2),
            'spent_amount' => round($spentAmount, 2),
            'remaining_amount' => $remainingAmount,
            'progress_percent' => $progressPercent,
            'status' => $status,
            'status_rank' => $this->getStatusRank($status),
            'is_active' => $target->is_active,
            'category' => $target->category ? [
                'id' => $target->category->id,
                'name' => $target->category->name,
                'icon' => $target->category->icon,
                'color' => $target->category->color,
            ] : null,
        ];
    }

    private function getCurrentPeriodRange(RecurringFrequency $frequency): array
    {
        $now = CarbonImmutable::now();

        return match ($frequency) {
            RecurringFrequency::Daily => [$now->startOfDay(), $now->endOfDay()],
            RecurringFrequency::Weekly => [$now->startOfWeek(), $now->endOfWeek()],
            RecurringFrequency::Monthly => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }

    private function getStatusRank(string $status): int
    {
        return match ($status) {
            'exceeded' => 2,
            'warning' => 1,
            default => 0,
        };
    }
}
