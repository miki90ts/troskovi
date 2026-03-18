<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    Filter,
    Plus,
    Search,
    Trash2,
    Pencil,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Expenses', href: '/expenses' },
];

const { success, error: showError } = useToast();
const { deleteTransaction } = useTransactions();

// Filters
const search = ref(props.filters.search ?? '');
const categoryFilter = ref(props.filters.category_id ?? '');
const paymentMethodFilter = ref(props.filters.payment_method ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const showFilters = ref(false);

function applyFilters() {
    const query: Record<string, string> = {};
    if (search.value) query.search = search.value;
    if (categoryFilter.value) query.category_id = categoryFilter.value;
    if (paymentMethodFilter.value)
        query.payment_method = paymentMethodFilter.value;
    if (dateFrom.value) query.date_from = dateFrom.value;
    if (dateTo.value) query.date_to = dateTo.value;
    router.get('/expenses', query, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = '';
    categoryFilter.value = '';
    paymentMethodFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/expenses', {}, { preserveState: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

// Form dialog
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

// Delete
const deleteTarget = ref<Transaction | null>(null);

async function handleDelete() {
    if (!deleteTarget.value) return;
    try {
        await deleteTransaction(deleteTarget.value.id);
        success('Expense deleted');
        deleteTarget.value = null;
        router.reload();
    } catch {
        showError('Failed to delete expense');
    }
}

function goToPage(page: number) {
    router.get(
        '/expenses',
        { ...props.filters, page: String(page) },
        { preserveState: true, preserveScroll: true },
    );
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Expenses" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Expenses</h1>
                    <p class="text-sm text-muted-foreground">
                        Track and manage your expenses
                    </p>
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" /> Add Expense
                </Button>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-col gap-3">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search expenses..."
                            class="pl-9"
                        />
                    </div>
                    <Button
                        variant="outline"
                        @click="showFilters = !showFilters"
                    >
                        <Filter class="mr-2 h-4 w-4" /> Filters
                    </Button>
                </div>

                <div
                    v-if="showFilters"
                    class="flex flex-wrap gap-3 rounded-lg border bg-card p-4"
                >
                    <div class="grid gap-1">
                        <label class="text-xs text-muted-foreground"
                            >Category</label
                        >
                        <Select
                            v-model="categoryFilter"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="All" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All</SelectItem>
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
                    <div class="grid gap-1">
                        <label class="text-xs text-muted-foreground"
                            >Payment</label
                        >
                        <Select
                            v-model="paymentMethodFilter"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="All" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All</SelectItem>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="bank_account"
                                    >Bank Account</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-1">
                        <label class="text-xs text-muted-foreground"
                            >From</label
                        >
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="w-36"
                            @change="applyFilters"
                        />
                    </div>
                    <div class="grid gap-1">
                        <label class="text-xs text-muted-foreground">To</label>
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="w-36"
                            @change="applyFilters"
                        />
                    </div>
                    <div class="flex items-end">
                        <Button variant="ghost" size="sm" @click="clearFilters"
                            >Clear</Button
                        >
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm">
                <div v-if="transactions.data.length === 0">
                    <EmptyState
                        title="No expenses found"
                        description="No expenses match your filters, or you haven't added any yet."
                    >
                        <Button @click="openCreate">
                            <Plus class="mr-2 h-4 w-4" /> Add Expense
                        </Button>
                    </EmptyState>
                </div>
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Date</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Payment</TableHead>
                            <TableHead class="text-right">Amount</TableHead>
                            <TableHead class="w-20" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tx in transactions.data" :key="tx.id">
                            <TableCell class="text-sm text-muted-foreground">{{
                                formatDate(tx.date)
                            }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <ArrowDownCircle
                                        class="h-4 w-4 text-red-500"
                                    />
                                    <span class="font-medium">{{
                                        tx.description
                                    }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span
                                    v-if="tx.category"
                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs"
                                >
                                    {{ tx.category.name }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >—</span
                                >
                            </TableCell>
                            <TableCell class="text-sm capitalize">{{
                                tx.payment_method.replace('_', ' ')
                            }}</TableCell>
                            <TableCell class="text-right">
                                <CurrencyDisplay
                                    :amount="-tx.amount"
                                    colored
                                    class="font-semibold"
                                />
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7"
                                        @click="openEdit(tx)"
                                    >
                                        <Pencil class="h-3 w-3" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 text-destructive"
                                        @click="deleteTarget = tx"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div
                    v-if="transactions.meta.last_page > 1"
                    class="flex items-center justify-between border-t px-6 py-3"
                >
                    <p class="text-sm text-muted-foreground">
                        Showing {{ transactions.meta.from }}–{{
                            transactions.meta.to
                        }}
                        of {{ transactions.meta.total }}
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
            </div>
        </div>

        <!-- Transaction Form Dialog -->
        <TransactionFormDialog
            :open="showForm"
            :transaction="editingTransaction"
            :categories="categories.data"
            :accounts="accounts"
            default-type="expense"
            @close="showForm = false"
            @saved="onSaved"
        />

        <!-- Delete Confirm -->
        <ConfirmDialog
            :open="!!deleteTarget"
            title="Delete Expense"
            description="This expense will be permanently deleted. This action cannot be undone."
            confirm-text="Delete"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
