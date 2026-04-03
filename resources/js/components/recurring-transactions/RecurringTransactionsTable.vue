<script setup lang="ts">
import { computed } from 'vue';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    CalendarClock,
    Pencil,
    Plus,
    Power,
} from 'lucide-vue-next';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import PaymentMethodBadge from '@/components/transactions/PaymentMethodBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { t } from '@/lib/i18n';
import type { RecurringTransaction } from '@/types/models';

const props = defineProps<{
    type: 'expense' | 'income';
    transactions: RecurringTransaction[];
    accountsCount: number;
}>();

const emit = defineEmits<{
    create: [];
    edit: [transaction: RecurringTransaction];
    deactivate: [transaction: RecurringTransaction];
}>();

const isExpense = computed(() => props.type === 'expense');
const sectionLabel = computed(() =>
    isExpense.value
        ? t('finance.recurring.expenseLabel')
        : t('finance.recurring.incomeLabel'),
);
const sectionDescription = computed(() =>
    isExpense.value
        ? t('finance.recurring.expenseSectionDescription')
        : t('finance.recurring.incomeSectionDescription'),
);
const emptyTitle = computed(() =>
    isExpense.value
        ? t('finance.recurring.emptyExpenseTitle')
        : t('finance.recurring.emptyIncomeTitle'),
);
const emptyDescription = computed(() =>
    isExpense.value
        ? t('finance.recurring.emptyExpenseDescription')
        : t('finance.recurring.emptyIncomeDescription'),
);
const emptyAction = computed(() =>
    isExpense.value
        ? t('finance.recurring.emptyExpenseAction')
        : t('finance.recurring.emptyIncomeAction'),
);

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function recurringAccountLabel(item: RecurringTransaction): string {
    if (item.bank_account?.name) {
        return item.bank_account.name;
    }

    return item.payment_method === 'bank_account'
        ? t('common.states.noLinkedAccount')
        : t('common.states.cashWallet');
}

function frequencyLabel(frequency: RecurringTransaction['frequency']): string {
    return (
        {
            daily: 'Dnevno',
            weekly: t('common.recurringFrequencies.weekly'),
            monthly: t('common.recurringFrequencies.monthly'),
        }[frequency] ?? frequency
    );
}
</script>

<template>
    <div>
        <div
            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ sectionLabel }}
                </p>
                <h3 class="mt-1 text-lg font-semibold tracking-tight">
                    {{ sectionDescription }}
                </h3>
            </div>
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>
                    {{
                        t('finance.categories.totalCount', {
                            count: props.transactions.length,
                        })
                    }}
                </span>
                <span class="hidden h-1 w-1 rounded-full bg-border sm:block" />
                <span>
                    {{
                        t('finance.recurring.availableAccounts', {
                            count: props.accountsCount,
                        })
                    }}
                </span>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
        >
            <div v-if="props.transactions.length === 0">
                <EmptyState :title="emptyTitle" :description="emptyDescription">
                    <Button class="rounded-2xl px-5" @click="emit('create')">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ emptyAction }}
                    </Button>
                </EmptyState>
            </div>
            <Table v-else>
                <TableHeader class="bg-muted/30">
                    <TableRow>
                        <TableHead>{{
                            t('common.labels.description')
                        }}</TableHead>
                        <TableHead>{{
                            t('common.labels.frequency')
                        }}</TableHead>
                        <TableHead>{{ t('common.labels.nextDate') }}</TableHead>
                        <TableHead>{{ t('common.labels.category') }}</TableHead>
                        <TableHead>{{ t('common.labels.account') }}</TableHead>
                        <TableHead class="text-right">{{
                            t('common.labels.amount')
                        }}</TableHead>
                        <TableHead class="w-24" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="item in props.transactions"
                        :key="item.id"
                        class="border-border/60"
                    >
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl"
                                    :class="
                                        isExpense
                                            ? 'bg-red-500/10'
                                            : 'bg-green-500/10'
                                    "
                                >
                                    <ArrowDownCircle
                                        v-if="isExpense"
                                        class="h-4 w-4 text-red-500"
                                    />
                                    <ArrowUpCircle
                                        v-else
                                        class="h-4 w-4 text-green-500"
                                    />
                                </div>
                                <div>
                                    <p class="font-medium">
                                        {{ item.description }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{
                                            t(
                                                'finance.recurring.lastProcessed',
                                                {
                                                    date: item.last_processed_date
                                                        ? formatDate(
                                                              item.last_processed_date,
                                                          )
                                                        : t(
                                                              'common.states.never',
                                                          ),
                                                },
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>{{
                            frequencyLabel(item.frequency)
                        }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2 text-sm">
                                <CalendarClock
                                    class="h-4 w-4 text-muted-foreground"
                                />
                                {{ formatDate(item.next_due_date) }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <CategoryBadge
                                v-if="item.category"
                                :category="item.category"
                                compact
                            />
                            <span v-else class="text-xs text-muted-foreground">
                                {{ t('common.states.uncategorized') }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <div class="space-y-1 text-sm">
                                <PaymentMethodBadge
                                    :payment-method="item.payment_method"
                                    compact
                                />
                                <span
                                    class="block text-xs text-muted-foreground"
                                >
                                    {{ recurringAccountLabel(item) }}
                                </span>
                            </div>
                        </TableCell>
                        <TableCell class="text-right">
                            <CurrencyDisplay
                                :amount="isExpense ? -item.amount : item.amount"
                                colored
                                class="font-semibold"
                            />
                        </TableCell>
                        <TableCell>
                            <div class="flex justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9 rounded-2xl"
                                    @click="emit('edit', item)"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9 rounded-2xl text-destructive"
                                    @click="emit('deactivate', item)"
                                >
                                    <Power class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
