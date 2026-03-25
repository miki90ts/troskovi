<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowUpCircle,
    CalendarRange,
    Filter,
    Landmark,
    Plus,
    ReceiptText,
    Search,
    SlidersHorizontal,
    Trash2,
    Pencil,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import TransactionFormDialog from '@/components/TransactionFormDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { useToast } from '@/composables/useToast';
import { useTransactions } from '@/composables/useTransactions';
import { formatCurrency, t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { Category, Transaction } from '@/types/models';
import type { PaginationMeta } from '@/types/api';

const props = defineProps<{
    transactions: { data: Transaction[]; meta: PaginationMeta };
    categories: { data: Category[] };
    accounts: { id: number; name: string }[];
    filters: Record<string, string | undefined>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.income'), href: '/incomes' },
];

const { success, error: showError } = useToast();
const { deleteTransaction } = useTransactions();

const search = ref(props.filters.search ?? '');
const categoryFilter = ref(props.filters.category_id ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const showFilters = ref(false);

const visibleAmountTotal = computed(() =>
    props.transactions.data.reduce((sum, tx) => sum + tx.amount, 0),
);

const averageIncome = computed(() =>
    props.transactions.data.length
        ? visibleAmountTotal.value / props.transactions.data.length
        : 0,
);

const categorizedCount = computed(
    () => props.transactions.data.filter((tx) => tx.category).length,
);

const accountLinkedCount = computed(
    () => props.transactions.data.filter((tx) => tx.bank_account).length,
);

const activeFiltersCount = computed(
    () =>
        [
            search.value,
            categoryFilter.value,
            dateFrom.value,
            dateTo.value,
        ].filter(Boolean).length,
);

function applyFilters() {
    const query: Record<string, string> = {};
    if (search.value) query.search = search.value;
    if (categoryFilter.value) query.category_id = categoryFilter.value;
    if (dateFrom.value) query.date_from = dateFrom.value;
    if (dateTo.value) query.date_to = dateTo.value;
    router.get('/incomes', query, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = '';
    categoryFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/incomes', {}, { preserveState: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

const showForm = ref(false);
const editingTransaction = ref<Transaction | null>(null);

function openCreate() {
    editingTransaction.value = null;
    showForm.value = true;
}

function openEdit(tx: Transaction) {
    editingTransaction.value = tx;
    showForm.value = true;
}

function onSaved() {
    showForm.value = false;
    router.reload();
}

const deleteTarget = ref<Transaction | null>(null);

async function handleDelete() {
    if (!deleteTarget.value) return;
    try {
        await deleteTransaction(deleteTarget.value.id);
        success(t('finance.incomes.deleted'));
        deleteTarget.value = null;
        router.reload();
    } catch {
        showError(t('finance.incomes.deleteError'));
    }
}

function goToPage(page: number) {
    router.get(
        '/incomes',
        { ...props.filters, page: String(page) },
        { preserveState: true, preserveScroll: true },
    );
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="t('finance.incomes.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-6 shadow-sm"
            >
                <div
                    class="absolute -top-16 -left-12 h-48 w-48 rounded-full bg-emerald-400/15 blur-3xl"
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
                            {{ t('finance.incomes.badge') }}
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-3xl font-semibold tracking-tight text-foreground"
                            >
                                {{ t('finance.incomes.heroTitle') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('finance.incomes.heroDescription') }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('common.labels.totalShown') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ formatCurrency(visibleAmountTotal) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.incomes.averageIncome') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ formatCurrency(averageIncome) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('common.labels.filteredRows') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ transactions.meta.total }}
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
                                    >{{ t('common.labels.categorized') }}</span
                                >
                                <ReceiptText class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-xl font-semibold">
                                {{ categorizedCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.incomes.linkedAccount')
                                    }}</span
                                >
                                <Landmark class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-xl font-semibold">
                                {{ accountLinkedCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{ t('common.labels.dateRange') }}</span
                                >
                                <CalendarRange class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-sm leading-5 font-semibold">
                                {{ dateFrom || t('common.states.anyStart')
                                }}<br />{{
                                    dateTo || t('common.states.anyEnd')
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

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
                            {{ t('finance.incomes.recordsTitle') }}
                        </p>
                        <h2 class="mt-1 text-xl font-semibold tracking-tight">
                            {{ t('finance.incomes.recordsDescription') }}
                        </h2>
                    </div>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="relative min-w-0 flex-1 sm:w-72">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                :placeholder="
                                    t('finance.incomes.searchPlaceholder')
                                "
                                class="h-11 rounded-2xl border-border/60 bg-background pl-9"
                            />
                        </div>
                        <Button
                            variant="outline"
                            class="h-11 rounded-2xl border-border/60 px-4"
                            @click="showFilters = !showFilters"
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
                        <Button
                            class="h-11 rounded-2xl px-5"
                            @click="openCreate"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            {{ t('finance.incomes.add') }}
                        </Button>
                    </div>
                </div>

                <div
                    v-if="showFilters"
                    class="mt-5 grid gap-4 rounded-3xl border border-dashed border-border/70 bg-background/70 p-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div class="grid gap-2">
                        <label
                            class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('common.labels.category') }}
                        </label>
                        <Select
                            v-model="categoryFilter"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('finance.incomes.allCategories')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Sve</SelectItem>
                                <SelectItem
                                    v-for="cat in categories.data"
                                    :key="cat.id"
                                    :value="String(cat.id)"
                                >
                                    {{ cat.name }}
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
                            @change="applyFilters"
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
                            @change="applyFilters"
                        />
                    </div>
                    <div class="flex items-end">
                        <Button
                            variant="ghost"
                            class="h-11 w-full rounded-2xl"
                            @click="clearFilters"
                        >
                            <Filter class="mr-2 h-4 w-4" />
                            {{ t('common.actions.clearFilters') }}
                        </Button>
                    </div>
                </div>
            </section>

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
                            {{ t('finance.incomes.historyTitle') }}
                        </h2>
                    </div>
                    <div
                        class="flex items-center gap-3 text-sm text-muted-foreground"
                    >
                        <span>{{
                            t('common.labels.resultsTotal', {
                                count: transactions.meta.total,
                            })
                        }}</span>
                        <span
                            class="hidden h-1 w-1 rounded-full bg-border sm:block"
                        />
                        <span>{{
                            t('common.labels.connectedAccounts', {
                                count: accounts.length,
                            })
                        }}</span>
                    </div>
                </div>
                <div v-if="transactions.data.length === 0">
                    <EmptyState
                        :title="t('finance.incomes.emptyTitle')"
                        :description="t('finance.incomes.emptyDescription')"
                    >
                        <Button class="rounded-2xl px-5" @click="openCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ t('finance.incomes.add') }}
                        </Button>
                    </EmptyState>
                </div>
                <Table v-else>
                    <TableHeader class="bg-muted/30">
                        <TableRow>
                            <TableHead>{{ t('common.labels.date') }}</TableHead>
                            <TableHead>{{
                                t('common.labels.description')
                            }}</TableHead>
                            <TableHead>{{
                                t('common.labels.category')
                            }}</TableHead>
                            <TableHead>{{
                                t('common.labels.account')
                            }}</TableHead>
                            <TableHead class="text-right">{{
                                t('common.labels.amount')
                            }}</TableHead>
                            <TableHead class="w-20" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="tx in transactions.data"
                            :key="tx.id"
                            class="border-border/60"
                        >
                            <TableCell class="text-sm text-muted-foreground">{{
                                formatDate(tx.date)
                            }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-500/10"
                                    >
                                        <ArrowUpCircle
                                            class="h-4 w-4 text-green-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <span class="block font-medium">{{
                                            tx.description
                                        }}</span>
                                        <span
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                tx.notes ||
                                                t('common.states.noNotes')
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span
                                    v-if="tx.category"
                                    class="inline-flex items-center gap-1 rounded-full border border-border/60 bg-muted/50 px-3 py-1 text-xs font-medium"
                                >
                                    {{ tx.category.name }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ t('common.states.uncategorized') }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <div class="space-y-1 text-sm">
                                    <span class="block">{{
                                        tx.bank_account?.name ??
                                        t('common.states.noLinkedAccount')
                                    }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{
                                            tx.payment_method.replace('_', ' ')
                                        }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="space-y-1">
                                    <CurrencyDisplay
                                        :amount="tx.amount"
                                        colored
                                        class="font-semibold"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        {{ t('finance.incomes.incomeLabel') }}
                                    </p>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 rounded-2xl"
                                        @click="openEdit(tx)"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 rounded-2xl text-destructive"
                                        @click="deleteTarget = tx"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div
                    v-if="transactions.meta.last_page > 1"
                    class="flex flex-col gap-3 border-t border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-muted-foreground">
                        {{
                            t('common.labels.shownRange', {
                                from: transactions.meta.from ?? 0,
                                to: transactions.meta.to ?? 0,
                                total: transactions.meta.total,
                            })
                        }}
                    </p>
                    <Pagination
                        :items-per-page="transactions.meta.per_page"
                        :total="transactions.meta.total"
                        :page="transactions.meta.current_page"
                    >
                        <PaginationContent>
                            <PaginationItem
                                :value="transactions.meta.current_page - 1"
                            >
                                <PaginationPrevious
                                    :disabled="
                                        transactions.meta.current_page === 1
                                    "
                                    @click="
                                        goToPage(
                                            transactions.meta.current_page - 1,
                                        )
                                    "
                                />
                            </PaginationItem>
                            <PaginationItem
                                :value="transactions.meta.current_page + 1"
                            >
                                <PaginationNext
                                    :disabled="
                                        transactions.meta.current_page ===
                                        transactions.meta.last_page
                                    "
                                    @click="
                                        goToPage(
                                            transactions.meta.current_page + 1,
                                        )
                                    "
                                />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                </div>
            </section>
        </div>

        <TransactionFormDialog
            :open="showForm"
            :transaction="editingTransaction"
            :categories="categories.data"
            :accounts="accounts"
            default-type="income"
            @close="showForm = false"
            @saved="onSaved"
        />

        <ConfirmDialog
            :open="!!deleteTarget"
            :title="t('finance.incomes.deleteTitle')"
            :description="t('finance.incomes.deleteDescription')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
