<script setup lang="ts">
import {
    AlertTriangle,
    HandCoins,
    Plus,
    TrendingDown,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { t } from '@/lib/i18n';
import { formatCurrency } from '@/lib/i18n';
import type { DebtSummary } from '@/types/models';

defineProps<{
    summary: DebtSummary;
}>();

const emit = defineEmits<{
    create: [];
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
            <div class="max-w-4xl space-y-4">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                >
                    {{ t('debts.badge') }}
                </div>
                <div class="space-y-2">
                    <h1
                        class="text-3xl font-semibold tracking-tight text-foreground"
                    >
                        {{ t('debts.heroTitle') }}
                    </h1>
                    <p class="max-w-xl text-sm leading-6 text-muted-foreground">
                        {{ t('debts.heroDescription') }}
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ t('debts.iOweTotal') }}
                            </p>
                            <TrendingDown class="h-4 w-4 text-orange-500" />
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-orange-600">
                            {{ formatCurrency(summary.total_i_owe) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ t('debts.owedToMeTotal') }}
                            </p>
                            <TrendingUp class="h-4 w-4 text-emerald-500" />
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-emerald-600">
                            {{ formatCurrency(summary.total_owed_to_me) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ t('debts.activeDebts') }}
                            </p>
                            <Users class="h-4 w-4 text-primary" />
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ summary.active_count }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ t('debts.overdueDebts') }}
                            </p>
                            <AlertTriangle
                                class="h-4 w-4"
                                :class="
                                    summary.overdue_count > 0
                                        ? 'text-red-500'
                                        : 'text-muted-foreground'
                                "
                            />
                        </div>
                        <p
                            class="mt-2 text-2xl font-semibold"
                            :class="
                                summary.overdue_count > 0
                                    ? 'text-red-600'
                                    : 'text-foreground'
                            "
                        >
                            {{ summary.overdue_count }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 gap-3">
                <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('debts.add') }}
                </Button>
            </div>
        </div>
    </section>
</template>
