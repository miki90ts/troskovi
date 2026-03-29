<script setup lang="ts">
import {
    ArrowLeftRight,
    CircleDollarSign,
    Landmark,
    Plus,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { formatCurrency, t } from '@/lib/i18n';

defineProps<{
    activeCount: number;
    archivedCount: number;
    totalBalance: number;
    connectedBanks: number;
}>();

const emit = defineEmits<{
    create: [];
    transfer: [];
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
            <div class="max-w-2xl space-y-4">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                >
                    {{ t('finance.bankAccounts.badge') }}
                </div>
                <div class="space-y-2">
                    <h1
                        class="text-3xl font-semibold tracking-tight text-foreground"
                    >
                        {{ t('finance.bankAccounts.heroTitle') }}
                    </h1>
                    <p class="max-w-xl text-sm leading-6 text-muted-foreground">
                        {{ t('finance.bankAccounts.heroDescription') }}
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.bankAccounts.activeAccounts') }}
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ activeCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.bankAccounts.archived') }}
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ archivedCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.bankAccounts.totalNetworkBalance') }}
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ formatCurrency(totalBalance) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-md">
                <div
                    class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.banks') }}</span
                        >
                        <Landmark class="h-4 w-4 text-primary" />
                    </div>
                    <p class="mt-3 text-lg font-semibold">
                        {{ connectedBanks }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.transfers') }}</span
                        >
                        <ArrowLeftRight class="h-4 w-4 text-primary" />
                    </div>
                    <p class="mt-3 text-sm font-semibold">
                        {{ t('finance.bankAccounts.transfersDescription') }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.visibility') }}</span
                        >
                        <CircleDollarSign class="h-4 w-4 text-primary" />
                    </div>
                    <p class="mt-3 text-sm font-semibold">
                        {{ t('finance.bankAccounts.visibilityDescription') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <Button
                variant="outline"
                class="h-11 rounded-2xl border-border/60 px-4"
                @click="emit('transfer')"
            >
                <ArrowLeftRight class="mr-2 h-4 w-4" />
                {{ t('common.actions.transfer') }}
            </Button>
            <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('finance.bankAccounts.add') }}
            </Button>
        </div>
    </section>
</template>
