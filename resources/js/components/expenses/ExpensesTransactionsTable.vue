<script setup lang="ts">
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

defineProps<{
    transactions: Transaction[];
    pagination: PaginationMeta;
    accountsCount: number;
}>();

const emit = defineEmits<{
    create: [];
    edit: [transaction: Transaction];
    delete: [transaction: Transaction];
    pageChange: [page: number];
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
</script>

<template>
    <section
        class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
    >
        <div
            class="flex flex-col gap-4 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
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
                                :amount="-tx.amount"
                                colored
                                class="font-semibold"
                            />
                            <p class="text-xs text-muted-foreground">
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
            v-if="pagination.last_page > 1"
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
