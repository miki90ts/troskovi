<script setup lang="ts">
import type { AcceptableValue } from 'reka-ui';
import {
    ArrowDownCircle,
    Pencil,
    Plus,
    ShieldCheck,
    Trash2,
} from 'lucide-vue-next';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import PaymentMethodBadge from '@/components/transactions/PaymentMethodBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
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
import type { Transaction } from '@/types/models';

const props = defineProps<{
    transactions: Transaction[];
    pagination: PaginationMeta;
    accountsCount: number;
    perPage: string;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
}>();

const perPageOptions = ['15', '30', '50', '100'] as const;

const emit = defineEmits<{
    create: [];
    edit: [transaction: Transaction];
    delete: [transaction: Transaction];
    pageChange: [page: number];
    perPageChange: [value: string];
}>();

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function accountLabel(transaction: Transaction): string {
    if (transaction.bank_account?.name) {
        return transaction.bank_account.name;
    }

    return transaction.payment_method === 'bank_account'
        ? t('common.states.noLinkedAccount')
        : t('common.states.cashWallet');
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

function displayAmount(tx: Transaction): number {
    const targetRate = resolveRate(props.defaultCurrencyCode);

    if (!targetRate) {
        return tx.amount;
    }

    return Number((tx.base_amount / targetRate).toFixed(2));
}
</script>

<template>
    <section
        class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
    >
        <div
            class="flex flex-col gap-4 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <div>
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('common.labels.transactions') }}
                    </p>
                    <h2 class="mt-1 text-lg font-semibold">
                        {{ t('finance.expenses.historyTitle') }}
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-muted-foreground">
                        {{ t('common.labels.rowsPerPage') }}
                    </span>
                    <Select
                        :model-value="props.perPage"
                        @update:model-value="handlePerPageChange"
                    >
                        <SelectTrigger
                            class="h-10 w-23 rounded-2xl border-border/60 bg-background"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in perPageOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>{{
                    t('common.labels.resultsTotal', { count: pagination.total })
                }}</span>
                <span class="hidden h-1 w-1 rounded-full bg-border sm:block" />
                <span>
                    {{
                        t('common.labels.connectedAccounts', {
                            count: accountsCount,
                        })
                    }}
                </span>
            </div>
        </div>

        <div v-if="transactions.length === 0">
            <EmptyState
                :title="t('finance.expenses.emptyTitle')"
                :description="t('finance.expenses.emptyDescription')"
            >
                <Button class="rounded-2xl px-5" @click="emit('create')">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('finance.expenses.add') }}
                </Button>
            </EmptyState>
        </div>

        <Table v-else>
            <TableHeader class="bg-muted/30">
                <TableRow>
                    <TableHead>{{ t('common.labels.date') }}</TableHead>
                    <TableHead>{{ t('common.labels.description') }}</TableHead>
                    <TableHead>{{ t('common.labels.category') }}</TableHead>
                    <TableHead>{{
                        t('common.labels.paymentMethod')
                    }}</TableHead>
                    <TableHead class="text-right">{{
                        t('common.labels.amount')
                    }}</TableHead>
                    <TableHead class="w-20" />
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="tx in transactions"
                    :key="tx.id"
                    class="border-border/60"
                >
                    <TableCell class="text-sm text-muted-foreground">
                        {{ formatDate(tx.date) }}
                    </TableCell>
                    <TableCell>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-500/10"
                            >
                                <ArrowDownCircle class="h-4 w-4 text-red-500" />
                            </div>
                            <div class="space-y-1">
                                <span class="block font-medium">
                                    {{ tx.description }}
                                    <ShieldCheck
                                        v-if="tx.is_warranty"
                                        class="ml-1 inline h-3.5 w-3.5 text-emerald-500"
                                        :title="
                                            t(
                                                'components.transactionForm.warranty',
                                            )
                                        "
                                    />
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ tx.notes || t('common.states.noNotes') }}
                                </span>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell>
                        <CategoryBadge
                            v-if="tx.category"
                            :category="tx.category"
                            compact
                        />
                        <span v-else class="text-xs text-muted-foreground">
                            {{ t('common.states.uncategorized') }}
                        </span>
                    </TableCell>
                    <TableCell>
                        <div class="space-y-1 text-sm">
                            <PaymentMethodBadge
                                :payment-method="tx.payment_method"
                                compact
                            />
                            <span class="block text-xs text-muted-foreground">
                                {{ accountLabel(tx) }}
                            </span>
                        </div>
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="space-y-1">
                            <CurrencyDisplay
                                :amount="-displayAmount(tx)"
                                :currency="props.defaultCurrencyCode"
                                colored
                                class="font-semibold"
                            />
                            <p
                                v-if="
                                    tx.currency?.iso_code &&
                                    tx.currency.iso_code !==
                                        props.defaultCurrencyCode
                                "
                                class="text-xs text-muted-foreground"
                            >
                                <CurrencyDisplay
                                    :amount="-tx.amount"
                                    :currency="tx.currency.iso_code"
                                />
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                {{ t('finance.expenses.expenseLabel') }}
                            </p>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex justify-end gap-1">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 rounded-2xl"
                                @click="emit('edit', tx)"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 rounded-2xl text-destructive"
                                @click="emit('delete', tx)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div
            v-if="transactions.length > 0"
            class="flex flex-col gap-3 border-t border-border/60 px-5 py-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <p class="text-sm text-muted-foreground">
                    {{
                        t('common.labels.shownRange', {
                            from: pagination.from ?? 0,
                            to: pagination.to ?? 0,
                            inTotal: pagination.total,
                        })
                    }}
                </p>
            </div>
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
                                emit('pageChange', pagination.current_page - 1)
                            "
                        />
                    </PaginationItem>
                    <PaginationItem :value="pagination.current_page + 1">
                        <PaginationNext
                            :disabled="
                                pagination.current_page === pagination.last_page
                            "
                            @click="
                                emit('pageChange', pagination.current_page + 1)
                            "
                        />
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    </section>
</template>
