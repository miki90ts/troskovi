<script setup lang="ts">
import { formatCurrency, t } from '@/lib/i18n';

defineProps<{
    visibleAmountTotal: number;
    averageIncome: number;
    accountLinkedCount: number;
}>();
</script>

<template>
    <section
        class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-6 shadow-sm"
    >
        <div
            class="absolute -top-16 -left-12 h-48 w-48 rounded-full bg-primary/15 blur-3xl"
        />
        <div
            class="absolute right-0 bottom-0 hidden h-56 w-56 rounded-full bg-emerald-300/10 blur-3xl lg:block"
        />
        <div
            class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
        >
            <div class="max-w-6xl space-y-4">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                >
                    {{ t('finance.incomes.badge') }}
                </div>
                <div class="space-y-2">
                    <h1
                        class="text-3xl font-semibold tracking-tight text-foreground"
                    >
                        {{ t('finance.incomes.heroTitle') }}
                    </h1>
                    <p class="max-w-xl text-sm leading-6 text-muted-foreground">
                        {{ t('finance.incomes.heroDescription') }}
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('common.labels.totalShown') }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-foreground">
                            {{ formatCurrency(visibleAmountTotal) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.incomes.averageIncome') }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-foreground">
                            {{ formatCurrency(averageIncome) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.incomes.linkedAccount') }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-foreground">
                            {{ accountLinkedCount }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
