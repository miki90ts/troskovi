<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PiggyBank } from 'lucide-vue-next';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import EmptyState from '@/components/EmptyState.vue';
import LoadingSkeleton from '@/components/LoadingSkeleton.vue';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency, t } from '@/lib/i18n';
import type {
    SpendingTargetPeriod,
    SpendingTargetProgress,
    SpendingTargetProgressResponse,
} from '@/types/models';

defineProps<{
    budgetPeriod: SpendingTargetPeriod;
    budgetLoading: boolean;
    budgetProgress: SpendingTargetProgressResponse | null;
}>();

const emit = defineEmits<{
    'update:budgetPeriod': [value: SpendingTargetPeriod];
}>();

function budgetStatusLabel(status: SpendingTargetProgress['status']) {
    return t(
        `finance.reports.status${status.charAt(0).toUpperCase()}${status.slice(1)}`,
    );
}

function budgetStatusClass(status: SpendingTargetProgress['status']) {
    return {
        exceeded:
            'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        warning:
            'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        ok: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    }[status];
}

function budgetBarClass(status: SpendingTargetProgress['status']) {
    return {
        exceeded: 'bg-rose-500',
        warning: 'bg-amber-500',
        ok: 'bg-emerald-500',
    }[status];
}

function budgetBarWidth(progressPercent: number) {
    return `${Math.min(progressPercent, 100)}%`;
}
</script>

<template>
    <section
        class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="max-w-2xl">
                <div class="flex items-center gap-2 text-primary">
                    <PiggyBank class="h-4 w-4" />
                    <p class="text-xs font-medium tracking-[0.24em] uppercase">
                        {{ t('finance.reports.budgetsTitle') }}
                    </p>
                </div>
                <h3 class="mt-2 text-xl font-semibold tracking-tight">
                    {{ t('finance.reports.budgetsDescription') }}
                </h3>
                <p
                    v-if="budgetProgress"
                    class="mt-2 text-sm text-muted-foreground"
                >
                    {{
                        t('finance.reports.warningNote', {
                            value: budgetProgress.warning_threshold_percent,
                        })
                    }}
                </p>
            </div>

            <div class="flex flex-col gap-3 lg:items-end">
                <Tabs
                    :model-value="budgetPeriod"
                    @update:model-value="
                        emit(
                            'update:budgetPeriod',
                            $event as SpendingTargetPeriod,
                        )
                    "
                >
                    <TabsList
                        class="grid h-auto grid-cols-3 rounded-2xl border border-border/60 bg-background/85 p-1"
                    >
                        <TabsTrigger
                            value="daily"
                            class="rounded-xl px-4 py-2.5"
                        >
                            {{ t('common.recurringFrequencies.daily') }}
                        </TabsTrigger>
                        <TabsTrigger
                            value="weekly"
                            class="rounded-xl px-4 py-2.5"
                        >
                            {{ t('common.recurringFrequencies.weekly') }}
                        </TabsTrigger>
                        <TabsTrigger
                            value="monthly"
                            class="rounded-xl px-4 py-2.5"
                        >
                            {{ t('common.recurringFrequencies.monthly') }}
                        </TabsTrigger>
                    </TabsList>
                </Tabs>

                <Link
                    href="/settings/budgets"
                    class="inline-flex items-center justify-center rounded-2xl border border-border/60 bg-background/85 px-4 py-2.5 text-sm font-medium transition hover:border-primary/30 hover:text-primary"
                >
                    {{ t('finance.reports.manageBudgets') }}
                </Link>
            </div>
        </div>

        <LoadingSkeleton v-if="budgetLoading" class="mt-5" :rows="4" />

        <template v-else-if="budgetProgress">
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-border/60 bg-background/80 p-4"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.reports.budgetsRange') }}
                    </p>
                    <p class="mt-2 text-lg font-semibold">
                        {{
                            t(
                                `common.recurringFrequencies.${budgetProgress.period}`,
                            )
                        }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/80 p-4"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.reports.budgetsTracked') }}
                    </p>
                    <p class="mt-2 text-lg font-semibold">
                        {{ budgetProgress.summary.active_count }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/80 p-4"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.reports.budgetsWarnings') }}
                    </p>
                    <p class="mt-2 text-lg font-semibold">
                        {{
                            budgetProgress.summary.warning_count +
                            budgetProgress.summary.exceeded_count
                        }}
                    </p>
                </div>
            </div>

            <EmptyState
                v-if="budgetProgress.targets.length === 0"
                :title="t('finance.reports.budgetEmptyTitle')"
                :description="t('finance.reports.budgetEmptyDescription')"
            >
                <Link
                    href="/settings/budgets"
                    class="inline-flex items-center justify-center rounded-2xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                >
                    {{ t('finance.reports.manageBudgets') }}
                </Link>
            </EmptyState>

            <div v-else class="mt-5 grid gap-4 xl:grid-cols-[1.1fr_1.6fr]">
                <div
                    class="rounded-3xl border border-border/60 bg-[linear-gradient(180deg,rgba(20,184,166,0.08),rgba(255,255,255,0.95))] p-5 dark:bg-[linear-gradient(180deg,rgba(20,184,166,0.12),rgba(15,23,42,0.88))]"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.reports.budgetOverallLabel') }}
                    </p>
                    <template v-if="budgetProgress.overall_target">
                        <div
                            class="mt-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <p class="text-3xl font-semibold">
                                    {{
                                        formatCurrency(
                                            budgetProgress.overall_target
                                                .spent_amount,
                                        )
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ t('finance.reports.budgetSpent') }}
                                    /
                                    {{
                                        formatCurrency(
                                            budgetProgress.overall_target
                                                .target_amount,
                                        )
                                    }}
                                </p>
                            </div>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    budgetStatusClass(
                                        budgetProgress.overall_target.status,
                                    )
                                "
                            >
                                {{
                                    budgetStatusLabel(
                                        budgetProgress.overall_target.status,
                                    )
                                }}
                            </span>
                        </div>
                        <div
                            class="mt-4 h-2.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-all"
                                :class="
                                    budgetBarClass(
                                        budgetProgress.overall_target.status,
                                    )
                                "
                                :style="{
                                    width: budgetBarWidth(
                                        budgetProgress.overall_target
                                            .progress_percent,
                                    ),
                                }"
                            />
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.budgetTarget') }}
                                </p>
                                <p class="mt-2 font-semibold">
                                    {{
                                        formatCurrency(
                                            budgetProgress.overall_target
                                                .target_amount,
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        budgetProgress.overall_target
                                            .remaining_amount >= 0
                                            ? t(
                                                  'finance.reports.budgetRemaining',
                                              )
                                            : t('finance.reports.budgetOver')
                                    }}
                                </p>
                                <p class="mt-2 font-semibold">
                                    {{
                                        formatCurrency(
                                            Math.abs(
                                                budgetProgress.overall_target
                                                    .remaining_amount,
                                            ),
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </template>
                    <EmptyState
                        v-else
                        :title="t('finance.reports.budgetEmptyTitle')"
                        :description="
                            t('finance.reports.budgetEmptyDescription')
                        "
                    />
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div
                        v-for="target in budgetProgress.targets"
                        :key="target.id"
                        class="rounded-3xl border border-border/60 bg-background/80 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <CategoryBadge
                                    v-if="target.category"
                                    :category="target.category"
                                    compact
                                />
                                <p v-else class="font-semibold text-foreground">
                                    {{
                                        t('finance.reports.budgetOverallLabel')
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ formatCurrency(target.spent_amount) }}
                                    /
                                    {{ formatCurrency(target.target_amount) }}
                                </p>
                            </div>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="budgetStatusClass(target.status)"
                            >
                                {{ budgetStatusLabel(target.status) }}
                            </span>
                        </div>
                        <div
                            class="mt-4 h-2.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-all"
                                :class="budgetBarClass(target.status)"
                                :style="{
                                    width: budgetBarWidth(
                                        target.progress_percent,
                                    ),
                                }"
                            />
                        </div>
                        <div
                            class="mt-4 flex items-center justify-between text-sm"
                        >
                            <span class="text-muted-foreground">{{
                                t('finance.reports.budgetSpent')
                            }}</span>
                            <span class="font-semibold"
                                >{{
                                    Math.round(target.progress_percent)
                                }}%</span
                            >
                        </div>
                        <div
                            class="mt-2 flex items-center justify-between text-sm text-muted-foreground"
                        >
                            <span>
                                {{
                                    target.remaining_amount >= 0
                                        ? t('finance.reports.budgetRemaining')
                                        : t('finance.reports.budgetOver')
                                }}
                            </span>
                            <span class="font-medium text-foreground">
                                {{
                                    formatCurrency(
                                        Math.abs(target.remaining_amount),
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </section>
</template>
