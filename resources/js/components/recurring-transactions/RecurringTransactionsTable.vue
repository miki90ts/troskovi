<script setup lang="ts">
import {
    ArrowDownCircle,
    ArrowUpCircle,
    CalendarClock,
    Pencil,
    Plus,
    Power,
    RotateCcw,
    Trash2,
} from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { computed } from 'vue';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import PaymentMethodBadge from '@/components/transactions/PaymentMethodBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { t } from '@/lib/i18n';
import type { PaginationMeta } from '@/types/api';
import type { RecurringTransaction } from '@/types/models';

const props = defineProps<{
    type: 'expense' | 'income';
    transactions: RecurringTransaction[];
    pagination: PaginationMeta;
    accountsCount: number;
    perPage: string;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
}>();

const perPageOptions = ['15', '30', '50', '100'] as const;

const emit = defineEmits<{
    create: [];
    edit: [transaction: RecurringTransaction];
    activate: [transaction: RecurringTransaction];
    deactivate: [transaction: RecurringTransaction];
    delete: [transaction: RecurringTransaction];
    pageChange: [page: number];
    perPageChange: [value: string];
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

function statusLabel(item: RecurringTransaction): string {
    return item.is_active
        ? t('finance.recurring.statusActive')
        : t('finance.recurring.statusInactive');
}

function debtTypeLabel(item: RecurringTransaction): string | null {
    if (!item.debt) {
        return null;
    }

    return item.debt.type === 'i_owe'
        ? t('debts.iOweLabel')
        : t('debts.owedToMeLabel');
}

function debtImpactLabel(item: RecurringTransaction): string | null {
    if (!item.debt) {
        return null;
    }

    if (item.debt.type === 'i_owe') {
        return item.type === 'expense' ? 'Smanjuje dug' : 'Povećava dug';
    }

    return item.type === 'income'
        ? 'Smanjuje potraživanje'
        : 'Povećava potraživanje';
}

function handlePerPageChange(value: AcceptableValue) {
    if (value !== null && value !== undefined) {
        emit('perPageChange', String(value));
    }
}

function resolveRate(currencyCode: string): number | null {
    if (currencyCode === 'RSD') {
        return 1;
    }

    const rate = props.latestExchangeRates[currencyCode];

    return typeof rate === 'number' && rate > 0 ? rate : null;
}

function displayAmount(item: RecurringTransaction): number {
    const sourceCode = item.currency?.iso_code ?? props.defaultCurrencyCode;
    const sourceRate = resolveRate(sourceCode);
    const targetRate = resolveRate(props.defaultCurrencyCode);

    if (!sourceRate || !targetRate) {
        return item.amount;
    }

    return Number(((item.amount * sourceRate) / targetRate).toFixed(2));
}
</script>

<template>
    <div>
        <div
            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-3">
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ sectionLabel }}
                </p>
                <h3 class="mt-1 text-lg font-semibold tracking-tight">
                    {{ sectionDescription }}
                </h3>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-muted-foreground">{{
                        t('common.labels.rowsPerPage')
                    }}</span>
                    <Select
                        :model-value="perPage"
                        @update:model-value="handlePerPageChange"
                    >
                        <SelectTrigger
                            class="h-10 w-23 rounded-2xl border-border/60 bg-background"
                            ><SelectValue
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in perPageOptions"
                                :key="option"
                                :value="option"
                                >{{ option }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>
                    {{
                        t('finance.categories.totalCount', {
                            count: props.pagination.total,
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
                        <TableHead class="w-36" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="item in props.transactions"
                        :key="item.id"
                        class="border-border/60 transition-colors"
                        :class="!item.is_active ? 'bg-muted/20' : ''"
                    >
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl transition-opacity"
                                    :class="
                                        isExpense
                                            ? 'bg-red-500/10'
                                            : 'bg-green-500/10'
                                    "
                                    :style="{
                                        opacity: item.is_active ? '1' : '0.5',
                                    }"
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
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p
                                            class="font-medium"
                                            :class="
                                                !item.is_active
                                                    ? 'text-muted-foreground'
                                                    : ''
                                            "
                                        >
                                            {{ item.description }}
                                        </p>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-[0.16em] uppercase"
                                            :class="
                                                item.is_active
                                                    ? 'bg-emerald-500/10 text-emerald-600'
                                                    : 'bg-muted text-muted-foreground'
                                            "
                                        >
                                            {{ statusLabel(item) }}
                                        </span>
                                        <span
                                            v-if="
                                                item.linked_transactions_count >
                                                0
                                            "
                                            class="rounded-full bg-muted px-2.5 py-1 text-[11px] font-semibold text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'finance.recurring.hasHistory',
                                                )
                                            }}
                                        </span>
                                    </div>
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
                                    <p
                                        v-if="item.debt"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ item.debt.person_name }} •
                                        {{ debtTypeLabel(item) }} •
                                        {{ debtImpactLabel(item) }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="space-y-1">
                                <p>{{ frequencyLabel(item.frequency) }}</p>
                                <p
                                    v-if="!item.is_active"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ t('finance.recurring.inactiveHint') }}
                                </p>
                            </div>
                        </TableCell>
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
                            <div class="space-y-1">
                                <CurrencyDisplay
                                    :amount="
                                        isExpense
                                            ? -displayAmount(item)
                                            : displayAmount(item)
                                    "
                                    :currency="defaultCurrencyCode"
                                    colored
                                    class="font-semibold"
                                    :class="!item.is_active ? 'opacity-60' : ''"
                                />
                                <p
                                    v-if="
                                        item.currency?.iso_code &&
                                        item.currency.iso_code !==
                                            defaultCurrencyCode
                                    "
                                    class="text-xs text-muted-foreground"
                                >
                                    <CurrencyDisplay
                                        :amount="
                                            isExpense
                                                ? -item.amount
                                                : item.amount
                                        "
                                        :currency="item.currency.iso_code"
                                    />
                                </p>
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex justify-end gap-1">
                                <Button
                                    v-if="item.is_active"
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9 rounded-2xl"
                                    :title="t('common.actions.edit')"
                                    @click="emit('edit', item)"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    v-if="item.is_active"
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9 rounded-2xl text-destructive"
                                    :title="
                                        t('finance.recurring.deactivateConfirm')
                                    "
                                    @click="emit('deactivate', item)"
                                >
                                    <Power class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    v-else
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9 rounded-2xl text-emerald-600"
                                    :title="t('finance.recurring.activate')"
                                    @click="emit('activate', item)"
                                >
                                    <RotateCcw class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    v-if="item.can_delete"
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9 rounded-2xl text-destructive"
                                    :title="t('common.actions.delete')"
                                    @click="emit('delete', item)"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <div
                v-if="props.transactions.length > 0"
                class="flex flex-col gap-3 border-t border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    {{
                        t('common.labels.shownRange', {
                            from: pagination.from ?? 0,
                            to: pagination.to ?? 0,
                            inTotal: pagination.total,
                        })
                    }}
                </p>
                <Pagination
                    v-if="pagination.last_page > 1"
                    :items-per-page="pagination.per_page"
                    :total="pagination.total"
                    :page="pagination.current_page"
                >
                    <PaginationContent>
                        <PaginationItem :value="pagination.current_page - 1">
                            <PaginationPrevious
                                :disabled="pagination.current_page === 1"
                                @click="
                                    emit(
                                        'pageChange',
                                        pagination.current_page - 1,
                                    )
                                "
                            />
                        </PaginationItem>
                        <PaginationItem :value="pagination.current_page + 1">
                            <PaginationNext
                                :disabled="
                                    pagination.current_page ===
                                    pagination.last_page
                                "
                                @click="
                                    emit(
                                        'pageChange',
                                        pagination.current_page + 1,
                                    )
                                "
                            />
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </div>
</template>
