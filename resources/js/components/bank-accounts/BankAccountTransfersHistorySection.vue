<script setup lang="ts">
import {
    ArrowRightLeft,
    Filter,
    Search,
    SlidersHorizontal,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import TransactionManagementShell from '@/components/transactions/TransactionManagementShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import type { AccountTransfer } from '@/types/models';

const props = defineProps<{
    transfers: AccountTransfer[];
}>();

const allAccountsValue = '__all_accounts__';
const itemsPerPage = 6;

const searchQuery = ref('');
const showFilters = ref(false);
const fromAccountFilter = ref(allAccountsValue);
const toAccountFilter = ref(allAccountsValue);
const dateFrom = ref('');
const dateTo = ref('');
const currentPage = ref(1);

const accountOptions = computed(() => {
    const seen = new Map<number, string>();

    for (const transfer of props.transfers) {
        seen.set(transfer.from_account.id, transfer.from_account.name);
        seen.set(transfer.to_account.id, transfer.to_account.name);
    }

    return Array.from(seen, ([id, name]) => ({ id, name })).sort(
        (left, right) => left.name.localeCompare(right.name, 'sr'),
    );
});

const filteredTransfers = computed(() => {
    const query = searchQuery.value.trim().toLocaleLowerCase('sr');

    return props.transfers.filter((transfer) => {
        const matchesQuery =
            query.length === 0 ||
            transfer.from_account.name
                .toLocaleLowerCase('sr')
                .includes(query) ||
            transfer.to_account.name.toLocaleLowerCase('sr').includes(query) ||
            (transfer.description ?? '')
                .toLocaleLowerCase('sr')
                .includes(query) ||
            String(transfer.amount).includes(query);

        const matchesFromAccount =
            fromAccountFilter.value === allAccountsValue ||
            String(transfer.from_account.id) === fromAccountFilter.value;

        const matchesToAccount =
            toAccountFilter.value === allAccountsValue ||
            String(transfer.to_account.id) === toAccountFilter.value;

        const matchesDateFrom =
            !dateFrom.value || transfer.date >= dateFrom.value;

        const matchesDateTo = !dateTo.value || transfer.date <= dateTo.value;

        return (
            matchesQuery &&
            matchesFromAccount &&
            matchesToAccount &&
            matchesDateFrom &&
            matchesDateTo
        );
    });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredTransfers.value.length / itemsPerPage)),
);

const paginatedTransfers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;

    return filteredTransfers.value.slice(start, start + itemsPerPage);
});

const shownFrom = computed(() => {
    if (filteredTransfers.value.length === 0) {
        return 0;
    }

    return (currentPage.value - 1) * itemsPerPage + 1;
});

const shownTo = computed(() =>
    Math.min(currentPage.value * itemsPerPage, filteredTransfers.value.length),
);

const hasActiveFilters = computed(
    () =>
        searchQuery.value.trim().length > 0 ||
        fromAccountFilter.value !== allAccountsValue ||
        toAccountFilter.value !== allAccountsValue ||
        dateFrom.value.length > 0 ||
        dateTo.value.length > 0,
);

const activeFiltersCount = computed(
    () =>
        Number(fromAccountFilter.value !== allAccountsValue) +
        Number(toAccountFilter.value !== allAccountsValue) +
        Number(dateFrom.value.length > 0) +
        Number(dateTo.value.length > 0),
);

function resetFilters() {
    searchQuery.value = '';
    fromAccountFilter.value = allAccountsValue;
    toAccountFilter.value = allAccountsValue;
    dateFrom.value = '';
    dateTo.value = '';
    currentPage.value = 1;
}

function previousPage() {
    currentPage.value = Math.max(1, currentPage.value - 1);
}

function nextPage() {
    currentPage.value = Math.min(totalPages.value, currentPage.value + 1);
}

function toggleFilters() {
    showFilters.value = !showFilters.value;
}

watch(
    [searchQuery, fromAccountFilter, toAccountFilter, dateFrom, dateTo],
    () => {
        currentPage.value = 1;
    },
);

watch(filteredTransfers, (nextTransfers) => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
    }

    if (nextTransfers.length === 0) {
        currentPage.value = 1;
    }
});

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <TransactionManagementShell
        :eyebrow="t('finance.bankAccounts.transfers')"
        :title="t('finance.bankAccounts.transfersHistoryTitle')"
    >
        <template #header-actions>
            <div class="relative min-w-0 flex-1 sm:w-72">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    :placeholder="
                        t('finance.bankAccounts.transferSearchPlaceholder')
                    "
                    class="h-11 rounded-2xl border-border/60 bg-background pl-9"
                />
            </div>

            <Button
                variant="outline"
                class="h-11 rounded-2xl border-border/60 px-4"
                @click="toggleFilters"
            >
                <SlidersHorizontal class="mr-2 h-4 w-4" />
                {{ t('common.actions.filters') }}
                <span
                    v-if="activeFiltersCount"
                    class="ml-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-primary/10 px-2 text-xs font-semibold text-primary"
                >
                    {{ activeFiltersCount }}
                </span>
            </Button>
        </template>

        <template #filters>
            <div
                v-if="showFilters"
                class="mt-5 grid gap-4 rounded-3xl border border-dashed border-border/70 bg-background/70 p-4 md:grid-cols-2 xl:grid-cols-5"
            >
                <div class="grid gap-2">
                    <label
                        class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.bankAccounts.fromAccount') }}
                    </label>
                    <Select v-model="fromAccountFilter">
                        <SelectTrigger
                            class="h-11 rounded-2xl border-border/60 bg-background"
                        >
                            <SelectValue
                                :placeholder="
                                    t('finance.bankAccounts.allAccounts')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="allAccountsValue">
                                {{ t('finance.bankAccounts.allAccounts') }}
                            </SelectItem>
                            <SelectItem
                                v-for="account in accountOptions"
                                :key="`from-${account.id}`"
                                :value="String(account.id)"
                            >
                                {{ account.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <label
                        class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.bankAccounts.toAccount') }}
                    </label>
                    <Select v-model="toAccountFilter">
                        <SelectTrigger
                            class="h-11 rounded-2xl border-border/60 bg-background"
                        >
                            <SelectValue
                                :placeholder="
                                    t('finance.bankAccounts.allAccounts')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="allAccountsValue">
                                {{ t('finance.bankAccounts.allAccounts') }}
                            </SelectItem>
                            <SelectItem
                                v-for="account in accountOptions"
                                :key="`to-${account.id}`"
                                :value="String(account.id)"
                            >
                                {{ account.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <label
                        class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('common.labels.from') }}
                    </label>
                    <Input
                        v-model="dateFrom"
                        type="date"
                        class="h-11 rounded-2xl border-border/60 bg-background"
                    />
                </div>

                <div class="grid gap-2">
                    <label
                        class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('common.labels.to') }}
                    </label>
                    <Input
                        v-model="dateTo"
                        type="date"
                        class="h-11 rounded-2xl border-border/60 bg-background"
                    />
                </div>

                <div class="flex items-end">
                    <Button
                        variant="ghost"
                        class="h-11 w-full rounded-2xl"
                        @click="resetFilters"
                    >
                        <Filter class="mr-2 h-4 w-4" />
                        {{ t('common.actions.clearFilters') }}
                    </Button>
                </div>
            </div>
        </template>

        <template #default>
            <div v-if="filteredTransfers.length === 0">
                <EmptyState
                    :title="
                        t('finance.bankAccounts.filteredTransfersEmptyTitle')
                    "
                    :description="
                        t(
                            'finance.bankAccounts.filteredTransfersEmptyDescription',
                        )
                    "
                >
                    <Button class="rounded-2xl px-5" @click="resetFilters">
                        <Filter class="mr-2 h-4 w-4" />
                        {{ t('common.actions.clearFilters') }}
                    </Button>
                </EmptyState>
            </div>

            <template v-else>
                <div
                    class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            {{
                                t('finance.bankAccounts.displayedTransfers', {
                                    count: filteredTransfers.length,
                                })
                            }}
                        </p>
                        <p
                            v-if="hasActiveFilters"
                            class="text-sm text-muted-foreground"
                        >
                            {{
                                t('common.labels.resultsTotal', {
                                    count: transfers.length,
                                })
                            }}
                        </p>
                    </div>

                    <Table>
                        <TableHeader class="bg-muted/30">
                            <TableRow>
                                <TableHead>{{
                                    t('common.labels.date')
                                }}</TableHead>
                                <TableHead>{{
                                    t('finance.bankAccounts.fromAccount')
                                }}</TableHead>
                                <TableHead>{{
                                    t('finance.bankAccounts.toAccount')
                                }}</TableHead>
                                <TableHead>{{
                                    t(
                                        'finance.bankAccounts.transferDescriptionLabel',
                                    )
                                }}</TableHead>
                                <TableHead class="text-right">{{
                                    t('common.labels.amount')
                                }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="transfer in paginatedTransfers"
                                :key="transfer.id"
                                class="border-border/60"
                            >
                                <TableCell
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ formatDate(transfer.date) }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500/10"
                                        >
                                            <ArrowRightLeft
                                                class="h-4 w-4 text-amber-600"
                                            />
                                        </div>
                                        <span class="font-medium">
                                            {{ transfer.from_account.name }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span class="font-medium">
                                        {{ transfer.to_account.name }}
                                    </span>
                                </TableCell>
                                <TableCell
                                    class="text-sm text-muted-foreground"
                                >
                                    {{
                                        transfer.description ||
                                        t(
                                            'finance.bankAccounts.noTransferDescription',
                                        )
                                    }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <CurrencyDisplay
                                        :amount="transfer.amount"
                                        colored
                                        class="font-semibold"
                                    />
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div
                        class="flex flex-col gap-3 border-t border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="space-y-1 text-sm text-muted-foreground">
                            <p>
                                {{
                                    t('common.labels.shownRange', {
                                        from: shownFrom,
                                        to: shownTo,
                                        inTotal: filteredTransfers.length,
                                    })
                                }}
                            </p>
                        </div>

                        <Pagination
                            v-if="totalPages > 1"
                            :items-per-page="itemsPerPage"
                            :total="filteredTransfers.length"
                            :page="currentPage"
                        >
                            <PaginationContent>
                                <PaginationItem :value="currentPage - 1">
                                    <PaginationPrevious
                                        :disabled="currentPage === 1"
                                        @click="previousPage"
                                    />
                                </PaginationItem>
                                <PaginationItem :value="currentPage + 1">
                                    <PaginationNext
                                        :disabled="currentPage === totalPages"
                                        @click="nextPage"
                                    />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                </div>
            </template>
        </template>
    </TransactionManagementShell>
</template>
