<script setup lang="ts">
import {
    Download,
    ExternalLink,
    Eye,
    ShieldCheck,
    ShieldX,
} from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
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
import {
    formatWarrantyDate,
    formatWarrantyDaysRemaining,
    getReceiptPreviewUrl,
    getWarrantyStatusBadge,
    getWarrantyStatusClass,
} from '@/lib/warranties';
import type { PaginationMeta } from '@/types/api';
import type { Transaction } from '@/types/models';

const props = defineProps<{
    transactions: Transaction[];
    pagination: PaginationMeta;
    perPage: string;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
}>();

const perPageOptions = ['15', '30', '50', '100'] as const;

const emit = defineEmits<{
    pageChange: [page: number];
    perPageChange: [value: string];
}>();

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
    <div
        class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
    >
        <div
            class="flex flex-col gap-4 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ t('finance.warranties.totalWarranties') }}
                </p>
                <h2 class="mt-1 text-lg font-semibold">
                    {{
                        t('common.labels.resultsTotal', {
                            count: pagination.total,
                        })
                    }}
                </h2>
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-sm text-muted-foreground">{{
                        t('common.labels.rowsPerPage')
                    }}</span>
                    <Select
                        :model-value="perPage"
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
        </div>

        <div v-if="transactions.length === 0">
            <EmptyState
                :title="t('finance.warranties.emptyTitle')"
                :description="t('finance.warranties.emptyDescription')"
            />
        </div>

        <Table v-else>
            <TableHeader class="bg-muted/30">
                <TableRow>
                    <TableHead>{{ t('common.labels.description') }}</TableHead>
                    <TableHead>{{
                        t('finance.warranties.purchaseDate')
                    }}</TableHead>
                    <TableHead>{{
                        t('finance.warranties.expiresAt')
                    }}</TableHead>
                    <TableHead>{{ t('common.labels.category') }}</TableHead>
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
                    <TableCell>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl"
                                :class="
                                    tx.warranty_is_expired
                                        ? 'bg-destructive/10'
                                        : 'bg-emerald-500/10'
                                "
                            >
                                <ShieldX
                                    v-if="tx.warranty_is_expired"
                                    class="h-4 w-4 text-destructive"
                                />
                                <ShieldCheck
                                    v-else
                                    class="h-4 w-4 text-emerald-500"
                                />
                            </div>
                            <div class="space-y-1">
                                <span class="block font-medium">{{
                                    tx.description
                                }}</span>
                                <span
                                    class="text-xs"
                                    :class="
                                        getWarrantyStatusClass(
                                            tx.warranty_expires_at,
                                        )
                                    "
                                >
                                    {{
                                        formatWarrantyDaysRemaining(
                                            tx.warranty_expires_at,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="text-sm text-muted-foreground">
                        {{ formatWarrantyDate(tx.date) }}
                    </TableCell>
                    <TableCell>
                        <div class="space-y-1">
                            <span class="block text-sm">
                                {{
                                    tx.warranty_expires_at
                                        ? formatWarrantyDate(
                                              tx.warranty_expires_at,
                                          )
                                        : '—'
                                }}
                            </span>
                            <span
                                v-if="tx.warranty_expires_at"
                                class="inline-flex rounded-full border px-2 py-0.5 text-xs font-medium"
                                :class="
                                    getWarrantyStatusBadge(
                                        tx.warranty_expires_at,
                                    ).class
                                "
                            >
                                {{
                                    getWarrantyStatusBadge(
                                        tx.warranty_expires_at,
                                    ).label
                                }}
                            </span>
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
                    <TableCell class="text-right">
                        <div class="space-y-1">
                            <CurrencyDisplay
                                :amount="-displayAmount(tx)"
                                :currency="defaultCurrencyCode"
                                colored
                                class="font-semibold"
                            />
                            <p
                                v-if="
                                    tx.currency?.iso_code &&
                                    tx.currency.iso_code !== defaultCurrencyCode
                                "
                                class="text-xs text-muted-foreground"
                            >
                                <CurrencyDisplay
                                    :amount="-tx.amount"
                                    :currency="tx.currency.iso_code"
                                />
                            </p>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex justify-end gap-1">
                            <a
                                v-if="tx.receipt_verification_url"
                                :href="tx.receipt_verification_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl text-primary transition-colors hover:bg-primary/10"
                                :title="
                                    t('components.transactionForm.qrOpenTax')
                                "
                            >
                                <ExternalLink class="h-3.5 w-3.5" />
                            </a>
                            <a
                                v-if="tx.receipt_url"
                                :href="getReceiptPreviewUrl(tx.receipt_url)"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                :title="t('finance.warranties.viewReceipt')"
                            >
                                <Eye class="h-3.5 w-3.5" />
                            </a>
                            <a
                                v-if="tx.receipt_url"
                                :href="tx.receipt_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                :title="t('finance.warranties.downloadReceipt')"
                            >
                                <Download class="h-3.5 w-3.5" />
                            </a>
                            <span
                                v-if="
                                    !tx.receipt_url &&
                                    !tx.receipt_verification_url
                                "
                                class="text-xs text-muted-foreground"
                            >
                                {{ t('finance.warranties.noReceipt') }}
                            </span>
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
    </div>
</template>
