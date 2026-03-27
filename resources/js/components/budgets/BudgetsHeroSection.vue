<script setup lang="ts">
import { AlertTriangle, PiggyBank } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { t } from '@/lib/i18n';

defineProps<{
    totalCount: number;
    activeCount: number;
    categoryCount: number;
    atRiskCount: number;
}>();
</script>

<template>
    <section
        class="space-y-6 rounded-[1.75rem] border border-border/70 bg-[linear-gradient(135deg,rgba(20,184,166,0.12),rgba(255,255,255,0.98))] p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8 dark:bg-[linear-gradient(135deg,rgba(20,184,166,0.18),rgba(15,23,42,0.9))]"
    >
        <div
            class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
        >
            <div class="space-y-4">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                >
                    <PiggyBank class="h-4 w-4" />
                    {{ t('settings.budgets.badge') }}
                </div>
                <Heading
                    variant="small"
                    :title="t('settings.budgets.title')"
                    :description="t('settings.budgets.description')"
                />
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:min-w-105 xl:grid-cols-4">
                <div
                    class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('settings.budgets.totalTargets') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ totalCount }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('settings.budgets.activeTargets') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ activeCount }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('settings.budgets.categoryTargets') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ categoryCount }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-amber-200/70 bg-amber-50/90 p-4 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-amber-700 uppercase dark:text-amber-300"
                    >
                        {{ t('settings.budgets.atRiskTargets') }}
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <AlertTriangle
                            class="h-5 w-5 text-amber-600 dark:text-amber-300"
                        />
                        <p
                            class="text-2xl font-semibold text-amber-800 dark:text-amber-100"
                        >
                            {{ atRiskCount }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
