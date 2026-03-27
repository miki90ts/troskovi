<script setup lang="ts">
import { TrendingDown, TrendingUp } from 'lucide-vue-next';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency, t } from '@/lib/i18n';
import type { ReportPeriod } from '@/types/api';
import type { ReportSummary } from '@/types/models';

defineProps<{
    period: ReportPeriod;
    formattedPeriodRange: string;
    summary: ReportSummary | null;
}>();

const emit = defineEmits<{
    'update:period': [value: ReportPeriod];
}>();

const periodLabels: Record<ReportPeriod, string> = {
    weekly: t('common.recurringFrequencies.weekly'),
    monthly: t('common.recurringFrequencies.monthly'),
    yearly: t('common.recurringFrequencies.yearly'),
};
</script>

<template>
    <section
        class="relative overflow-hidden rounded-3xl border border-border/60 bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.16),transparent_42%),linear-gradient(135deg,rgba(255,255,255,0.98),rgba(236,253,245,0.9))] p-6 shadow-sm dark:bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.22),transparent_38%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(13,148,136,0.14))]"
    >
        <div
            class="absolute inset-y-0 right-0 hidden w-1/3 bg-[radial-gradient(circle_at_center,rgba(45,212,191,0.16),transparent_62%)] lg:block"
        />
        <div
            class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
        >
            <div class="max-w-2xl space-y-4">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                >
                    {{ t('finance.reports.badge') }}
                </div>
                <div class="space-y-2">
                    <h1
                        class="text-3xl font-semibold tracking-tight text-foreground"
                    >
                        {{ t('finance.reports.heroTitle') }}
                    </h1>
                    <p class="max-w-xl text-sm leading-6 text-muted-foreground">
                        {{ t('finance.reports.heroDescription') }}
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.reports.reportRhythm') }}
                        </p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ periodLabels[period] }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.reports.coveredPeriod') }}
                        </p>
                        <p class="mt-2 text-sm leading-6 font-semibold">
                            {{ formattedPeriodRange }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.reports.monthlyChange') }}
                        </p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ summary ? `${summary.mom_change}%` : '...' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:items-end">
                <Tabs
                    :model-value="period"
                    @update:model-value="
                        emit('update:period', $event as ReportPeriod)
                    "
                >
                    <TabsList
                        class="grid h-auto grid-cols-3 rounded-2xl border border-border/60 bg-background/85 p-1"
                    >
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
                        <TabsTrigger
                            value="yearly"
                            class="rounded-xl px-4 py-2.5"
                        >
                            Godisnje
                        </TabsTrigger>
                    </TabsList>
                </Tabs>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-100">
                    <div
                        class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ t('finance.reports.biggestExpense') }}
                            </span>
                            <TrendingDown class="h-4 w-4 text-primary" />
                        </div>
                        <p class="mt-3 text-base font-semibold">
                            {{
                                summary?.biggest_expense_category ??
                                t('common.states.noDataYet')
                            }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                summary
                                    ? formatCurrency(
                                          summary.biggest_expense_amount,
                                      )
                                    : t('common.states.waitingData')
                            }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ t('finance.reports.savingsRate') }}
                            </span>
                            <TrendingUp class="h-4 w-4 text-primary" />
                        </div>
                        <p class="mt-3 text-base font-semibold">
                            {{
                                summary
                                    ? t('finance.reports.retained', {
                                          value: summary.savings_rate,
                                      })
                                    : t('common.states.waitingData')
                            }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                t('finance.reports.currentPeriodNote', {
                                    period: periodLabels[period].toLowerCase(),
                                })
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
