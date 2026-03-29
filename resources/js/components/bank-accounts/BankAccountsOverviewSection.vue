<script setup lang="ts">
import { Archive, Plus, RotateCcw } from 'lucide-vue-next';
import BankAccountCard from '@/components/bank-accounts/BankAccountCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { t } from '@/lib/i18n';
import type { BankAccount } from '@/types/models';

defineProps<{
    activeAccounts: BankAccount[];
    archivedAccounts: BankAccount[];
    connectedBanks: number;
}>();

const emit = defineEmits<{
    create: [];
    edit: [account: BankAccount];
    archive: [account: BankAccount];
    restore: [account: BankAccount];
}>();
</script>

<template>
    <div v-if="activeAccounts.length === 0 && archivedAccounts.length === 0">
        <EmptyState
            :title="t('finance.bankAccounts.emptyTitle')"
            :description="t('finance.bankAccounts.emptyDescription')"
        >
            <Button class="rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('finance.bankAccounts.add') }}
            </Button>
        </EmptyState>
    </div>

    <section
        v-else
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <div
            class="mb-5 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ t('finance.bankAccounts.overviewTitle') }}
                </p>
                <h2 class="mt-1 text-lg font-semibold tracking-tight">
                    {{ t('finance.bankAccounts.overviewDescription') }}
                </h2>
            </div>
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>{{
                    t('finance.bankAccounts.activeCount', {
                        count: activeAccounts.length,
                    })
                }}</span>
                <span class="hidden h-1 w-1 rounded-full bg-border sm:block" />
                <span>{{
                    t('finance.bankAccounts.banksCount', {
                        count: connectedBanks,
                    })
                }}</span>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <BankAccountCard
                v-for="account in activeAccounts"
                :key="account.id"
                :account="account"
                @edit="emit('edit', $event)"
                @archive="emit('archive', $event)"
            />
        </div>
    </section>

    <section
        v-if="archivedAccounts.length > 0"
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <h2 class="mb-3 text-lg font-semibold text-muted-foreground">
            {{ t('finance.bankAccounts.archived') }}
        </h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="account in archivedAccounts"
                :key="account.id"
                class="rounded-3xl border border-dashed border-border/70 bg-background/60 p-6 opacity-70"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Archive class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <p class="font-medium">
                                {{ account.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ account.bank_name }}
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="rounded-2xl"
                        @click="emit('restore', account)"
                    >
                        <RotateCcw class="mr-1 h-3 w-3" />
                        {{ t('finance.bankAccounts.restore') }}
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>
