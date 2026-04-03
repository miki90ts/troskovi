<script setup lang="ts">
import { AlertTriangle, ShieldCheck, Slash, Sparkles } from 'lucide-vue-next';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import { Button } from '@/components/ui/button';
import { formatCurrency, t } from '@/lib/i18n';
import type {
    BudgetStatusFilterOption,
    StatusFilter,
    TargetDisplayStatus,
    TargetWithProgress,
} from '@/types';

defineProps<{
    topRiskTargets: TargetWithProgress[];
    statusFilterOptions: BudgetStatusFilterOption[];
    statusFilter: StatusFilter;
    filteredCount: number;
}>();

const emit = defineEmits<{
    'update:statusFilter': [value: StatusFilter];
}>();

function labelForPeriod(period: TargetWithProgress['target']['period']) {
    return t(`common.recurringFrequencies.${period}`);
}

function statusLabel(status: TargetDisplayStatus) {
    return t(
        `settings.budgets.status${status.charAt(0).toUpperCase()}${status.slice(1)}`,
    );
}

function statusClass(status: TargetDisplayStatus) {
    return {
        exceeded:
            'border-rose-200 bg-rose-100/90 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-300',
        warning:
            'border-amber-200 bg-amber-100/90 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-300',
        ok: 'border-emerald-200 bg-emerald-100/90 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300',
        inactive:
            'border-border/70 bg-muted/70 text-muted-foreground dark:border-border/60 dark:bg-muted/30',
    }[status];
}

function progressBarClass(status: TargetDisplayStatus) {
    return {
        exceeded: 'bg-rose-500',
        warning: 'bg-amber-500',
        ok: 'bg-emerald-500',
        inactive: 'bg-muted-foreground/40',
    }[status];
}

function progressBarWidth(progressPercent?: number) {
    return `${Math.min(progressPercent ?? 0, 100)}%`;
}

function statusIcon(status: TargetDisplayStatus) {
    return {
        exceeded: AlertTriangle,
        warning: Sparkles,
        ok: ShieldCheck,
        inactive: Slash,
    }[status];
}
</script>

<template>
    <div class="grid gap-4 xl:grid-cols-[1.2fr_1.8fr]">
        <div
            class="rounded-3xl border border-border/60 bg-[linear-gradient(180deg,rgba(245,158,11,0.08),rgba(255,255,255,0.98))] p-5 dark:bg-[linear-gradient(180deg,rgba(245,158,11,0.16),rgba(15,23,42,0.88))]"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p
                        class="text-xs font-medium tracking-[0.24em] text-amber-700 uppercase dark:text-amber-300"
                    >
                        {{ t('settings.budgets.topRiskTitle') }}
                    </p>
                    <h2 class="mt-1 text-xl font-semibold tracking-tight">
                        {{ t('settings.budgets.topRiskDescription') }}
                    </h2>
                </div>
                <div
                    class="rounded-2xl border border-amber-200/80 bg-amber-100/80 p-2 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-300"
                >
                    <Sparkles class="h-4 w-4" />
                </div>
            </div>

            <div
                v-if="topRiskTargets.length === 0"
                class="mt-4 rounded-2xl border border-dashed border-border/70 bg-background/70 p-4 text-sm text-muted-foreground"
            >
                {{ t('settings.budgets.topRiskEmpty') }}
            </div>

            <div v-else class="mt-4 space-y-3">
                <div
                    v-for="entry in topRiskTargets"
                    :key="entry.target.id"
                    class="rounded-2xl border border-border/60 bg-background/80 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <CategoryBadge
                                v-if="entry.target.category"
                                :category="entry.target.category"
                                compact
                            />
                            <p v-else class="font-semibold text-foreground">
                                {{ t('settings.budgets.overallLabel') }}
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ labelForPeriod(entry.target.period) }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold"
                            :class="statusClass(entry.displayStatus)"
                        >
                            <component
                                :is="statusIcon(entry.displayStatus)"
                                class="h-3.5 w-3.5"
                            />
                            {{ statusLabel(entry.displayStatus) }}
                        </span>
                    </div>

                    <div v-if="entry.progress" class="mt-4 space-y-2">
                        <div
                            class="h-2.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-all"
                                :class="progressBarClass(entry.displayStatus)"
                                :style="{
                                    width: progressBarWidth(
                                        entry.progress.progress_percent,
                                    ),
                                }"
                            />
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">
                                {{
                                    formatCurrency(entry.progress.spent_amount)
                                }}
                                /
                                {{
                                    formatCurrency(entry.progress.target_amount)
                                }}
                            </span>
                            <span class="font-semibold text-foreground">
                                {{
                                    Math.round(entry.progress.progress_percent)
                                }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-border/60 bg-background/50 p-5">
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                    >
                        {{ t('settings.budgets.filterTitle') }}
                    </p>
                    <h2 class="mt-1 text-xl font-semibold tracking-tight">
                        {{ t('settings.budgets.filterDescription') }}
                    </h2>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-card/90 px-4 py-3 text-sm text-muted-foreground"
                >
                    {{
                        t('settings.budgets.filteredCount', {
                            count: filteredCount,
                        })
                    }}
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <Button
                    v-for="option in statusFilterOptions"
                    :key="option.value"
                    variant="outline"
                    class="rounded-full"
                    :class="
                        statusFilter === option.value
                            ? 'border-primary/30 bg-primary/10 text-primary'
                            : ''
                    "
                    @click="emit('update:statusFilter', option.value)"
                >
                    {{ option.label }}
                </Button>
            </div>
        </div>
    </div>
</template>
