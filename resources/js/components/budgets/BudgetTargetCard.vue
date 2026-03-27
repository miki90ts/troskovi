<script setup lang="ts">
import {
    AlertTriangle,
    Pencil,
    ShieldCheck,
    Slash,
    Sparkles,
    Trash2,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { formatCurrency, t } from '@/lib/i18n';
import type {
    SpendingTarget,
    TargetDisplayStatus,
    TargetWithProgress,
} from '@/types';

defineProps<{
    entry: TargetWithProgress;
}>();

const emit = defineEmits<{
    edit: [target: SpendingTarget];
    toggle: [target: SpendingTarget];
    delete: [target: SpendingTarget];
}>();

function labelForPeriod(period: TargetWithProgress['target']['period']) {
    return t(`common.recurringFrequencies.${period}`);
}

function targetLabel(target: TargetWithProgress['target']) {
    return target.category?.name ?? t('settings.budgets.overallLabel');
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

function remainingLabel(entry: TargetWithProgress) {
    if (!entry.progress) {
        return t('settings.budgets.progressPaused');
    }

    return entry.progress.remaining_amount >= 0
        ? t('settings.budgets.remaining')
        : t('settings.budgets.overBy');
}

function remainingValue(entry: TargetWithProgress) {
    if (!entry.progress) {
        return t('settings.budgets.progressPausedHint');
    }

    return formatCurrency(Math.abs(entry.progress.remaining_amount));
}
</script>

<template>
    <div
        class="rounded-3xl border border-border/60 bg-card/95 p-4 shadow-sm sm:p-5"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <div
                        class="h-2.5 w-2.5 rounded-full"
                        :style="{
                            backgroundColor:
                                entry.target.category?.color ??
                                'rgb(20 184 166)',
                        }"
                    />
                    <p class="font-semibold text-foreground">
                        {{ targetLabel(entry.target) }}
                    </p>
                </div>
                <div
                    class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
                >
                    <span>{{
                        formatCurrency(entry.target.target_amount)
                    }}</span>
                    <span>&middot;</span>
                    <span>{{ labelForPeriod(entry.target.period) }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
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
                <span
                    class="rounded-full px-3 py-1 text-[11px] font-semibold tracking-[0.16em] uppercase"
                    :class="
                        entry.target.is_active
                            ? 'bg-primary/10 text-primary'
                            : 'bg-muted text-muted-foreground'
                    "
                >
                    {{
                        entry.target.is_active
                            ? t('settings.budgets.active')
                            : t('settings.budgets.inactive')
                    }}
                </span>
            </div>
        </div>

        <div v-if="entry.progress" class="mt-5 space-y-3">
            <div class="h-2.5 overflow-hidden rounded-full bg-muted">
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
            <div class="grid gap-3 md:grid-cols-3">
                <div
                    class="rounded-2xl border border-border/60 bg-background/80 p-3"
                >
                    <p
                        class="text-[11px] font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('settings.budgets.spent') }}
                    </p>
                    <p class="mt-2 text-sm font-semibold">
                        {{ formatCurrency(entry.progress.spent_amount) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/80 p-3"
                >
                    <p
                        class="text-[11px] font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ remainingLabel(entry) }}
                    </p>
                    <p class="mt-2 text-sm font-semibold">
                        {{ remainingValue(entry) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/80 p-3"
                >
                    <p
                        class="text-[11px] font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('settings.budgets.progress') }}
                    </p>
                    <p class="mt-2 text-sm font-semibold">
                        {{ Math.round(entry.progress.progress_percent) }}%
                    </p>
                </div>
            </div>
        </div>

        <div
            v-else
            class="mt-5 rounded-2xl border border-dashed border-border/70 bg-muted/30 px-4 py-3 text-sm text-muted-foreground"
        >
            {{ t('settings.budgets.progressPausedHint') }}
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <Button
                variant="outline"
                size="sm"
                class="rounded-xl"
                @click="emit('edit', entry.target)"
            >
                <Pencil class="mr-2 h-4 w-4" />
                {{ t('common.actions.edit') }}
            </Button>
            <Button
                variant="outline"
                size="sm"
                class="rounded-xl"
                @click="emit('toggle', entry.target)"
            >
                {{
                    entry.target.is_active
                        ? t('settings.budgets.pause')
                        : t('settings.budgets.activate')
                }}
            </Button>
            <Button
                variant="outline"
                size="sm"
                class="rounded-xl text-destructive hover:text-destructive"
                @click="emit('delete', entry.target)"
            >
                <Trash2 class="mr-2 h-4 w-4" />
                {{ t('common.actions.delete') }}
            </Button>
        </div>
    </div>
</template>
