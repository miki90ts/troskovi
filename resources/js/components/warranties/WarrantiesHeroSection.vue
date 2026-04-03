<script setup lang="ts">
import { t } from '@/lib/i18n';

defineProps<{
    counts: {
        active: number;
        expiring_soon: number;
        expired: number;
    };
}>();
</script>

<template>
    <section
        class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-2 shadow-sm"
    >
        <div
            class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
        >
            <div class="max-w-6xl space-y-4">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                >
                    {{ t('finance.warranties.badge') }}
                </div>
                <div class="space-y-2">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-foreground"
                    >
                        {{ t('finance.warranties.heroTitle') }}
                    </h1>
                    <p class="max-w-xl text-sm leading-4 text-muted-foreground">
                        {{ t('finance.warranties.heroDescription') }}
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-2 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.warranties.activeWarranties') }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-emerald-500">
                            {{ counts.active }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-2 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.warranties.expiringSoon') }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-orange-500">
                            {{ counts.expiring_soon }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-2 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.warranties.expired') }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-destructive">
                            {{ counts.expired }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
