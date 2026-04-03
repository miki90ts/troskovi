<script setup lang="ts">
import { computed } from 'vue';
import { Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { t } from '@/lib/i18n';
import type { RecurringTransaction } from '@/types/models';
import type { RecurringTransactionTab } from '@/composables/useRecurringTransactionsPage';
import RecurringTransactionsTable from './RecurringTransactionsTable.vue';

const props = defineProps<{
    activeTab: RecurringTransactionTab;
    expenseTransactions: RecurringTransaction[];
    incomeTransactions: RecurringTransaction[];
    accountsCount: number;
}>();

const emit = defineEmits<{
    'update:activeTab': [value: RecurringTransactionTab];
    create: [];
    edit: [transaction: RecurringTransaction];
    deactivate: [transaction: RecurringTransaction];
}>();

const activeTabModel = computed({
    get: () => props.activeTab,
    set: (value: RecurringTransactionTab) => emit('update:activeTab', value),
});
</script>

<template>
    <section
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ t('finance.recurring.managementTitle') }}
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    {{ t('finance.recurring.managementDescription') }}
                </h2>
            </div>
            <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('finance.recurring.add') }}
            </Button>
        </div>

        <Tabs v-model="activeTabModel" class="mt-5 space-y-5">
            <TabsList
                class="grid h-auto w-full grid-cols-2 rounded-2xl border border-border/60 bg-background p-1 md:w-fit"
            >
                <TabsTrigger value="expense">
                    {{ t('finance.recurring.expenseTitle') }}
                </TabsTrigger>
                <TabsTrigger value="income">
                    {{ t('finance.recurring.incomeTitle') }}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="expense" class="mt-0">
                <RecurringTransactionsTable
                    type="expense"
                    :transactions="expenseTransactions"
                    :accounts-count="accountsCount"
                    @create="emit('create')"
                    @edit="emit('edit', $event)"
                    @deactivate="emit('deactivate', $event)"
                />
            </TabsContent>

            <TabsContent value="income" class="mt-0">
                <RecurringTransactionsTable
                    type="income"
                    :transactions="incomeTransactions"
                    :accounts-count="accountsCount"
                    @create="emit('create')"
                    @edit="emit('edit', $event)"
                    @deactivate="emit('deactivate', $event)"
                />
            </TabsContent>
        </Tabs>
    </section>
</template>
