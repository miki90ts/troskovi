import { router } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useToast } from '@/composables/useToast';
import { useTransactions } from '@/composables/useTransactions';
import type { PaginationMeta } from '@/types/api';
import type { Transaction } from '@/types/models';

export type TransactionListingFilters = Record<string, string | undefined>;

export type TransactionsPage = {
    data: Transaction[];
    meta: PaginationMeta;
};

export const ALL_CATEGORIES_VALUE = '__all_categories__';
export const ALL_PAYMENT_METHODS_VALUE = '__all_payment_methods__';
export const DEFAULT_PER_PAGE = '15';
export const PER_PAGE_OPTIONS = ['15', '30', '50', '100'] as const;

export function useTransactionListingPage(options: {
    transactionsPage: Ref<TransactionsPage>;
    filters: Ref<TransactionListingFilters>;
    routePath: string;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
    exportType: Transaction['type'];
    exportFilenamePrefix: string;
    deleteSuccessMessage: string;
    deleteErrorMessage: string;
    exportErrorMessage: string;
}) {
    const {
        transactionsPage,
        filters,
        routePath,
        defaultCurrencyCode,
        latestExchangeRates,
        exportType,
        exportFilenamePrefix,
        deleteSuccessMessage,
        deleteErrorMessage,
        exportErrorMessage,
    } = options;
    const { success, error: showError } = useToast();
    const { deleteTransaction } = useTransactions();

    const search = ref('');
    const categoryFilter = ref('');
    const paymentMethodFilter = ref('');
    const dateFrom = ref('');
    const dateTo = ref('');
    const perPage = ref(DEFAULT_PER_PAGE);
    const showFilters = ref(false);
    const showForm = ref(false);
    const editingTransaction = ref<Transaction | null>(null);
    const deleteTarget = ref<Transaction | null>(null);
    const exportingPdf = ref(false);

    let isSyncingFilters = false;
    let searchTimeout: ReturnType<typeof setTimeout> | undefined;

    const transactions = computed(() => transactionsPage.value.data);
    const pagination = computed(() => transactionsPage.value.meta);

    function resolveRate(currencyCode: string): number | null {
        if (currencyCode === 'RSD') {
            return 1;
        }

        const rate = latestExchangeRates[currencyCode];

        return typeof rate === 'number' && rate > 0 ? rate : null;
    }

    function convertFromBase(baseAmount: number): number {
        const targetRate = resolveRate(defaultCurrencyCode);

        if (!targetRate) {
            return baseAmount;
        }

        return Number((baseAmount / targetRate).toFixed(2));
    }

    const visibleAmountTotal = computed(() =>
        transactions.value.reduce(
            (sum, tx) => sum + convertFromBase(tx.base_amount ?? tx.amount),
            0,
        ),
    );
    const activeFiltersCount = computed(
        () =>
            [
                search.value,
                categoryFilter.value,
                paymentMethodFilter.value,
                dateFrom.value,
                dateTo.value,
            ].filter(Boolean).length,
    );

    const categoryFilterSelectValue = computed({
        get: () => categoryFilter.value,
        set: (value: string) => {
            categoryFilter.value = value;
        },
    });

    const paymentMethodFilterSelectValue = computed({
        get: () => paymentMethodFilter.value || ALL_PAYMENT_METHODS_VALUE,
        set: (value: string) => {
            paymentMethodFilter.value =
                value === ALL_PAYMENT_METHODS_VALUE ? '' : value;
        },
    });

    function syncFilterState(nextFilters: TransactionListingFilters) {
        isSyncingFilters = true;

        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = undefined;
        }

        search.value = nextFilters.search ?? '';
        categoryFilter.value =
            nextFilters.category_ids ?? nextFilters.category_id ?? '';
        paymentMethodFilter.value = nextFilters.payment_method ?? '';
        dateFrom.value = nextFilters.date_from ?? '';
        dateTo.value = nextFilters.date_to ?? '';
        perPage.value =
            nextFilters.per_page ??
            String(transactionsPage.value.meta.per_page ?? DEFAULT_PER_PAGE);

        void nextTick(() => {
            isSyncingFilters = false;
        });
    }

    function buildQuery(): Record<string, string> {
        const query: Record<string, string> = {};

        if (search.value) {
            query.search = search.value;
        }

        if (categoryFilter.value) {
            query.category_ids = categoryFilter.value;
        }

        if (paymentMethodFilter.value) {
            query.payment_method = paymentMethodFilter.value;
        }

        if (dateFrom.value) {
            query.date_from = dateFrom.value;
        }

        if (dateTo.value) {
            query.date_to = dateTo.value;
        }

        query.per_page = perPage.value;

        return query;
    }

    function applyFilters() {
        router.get(routePath, buildQuery(), {
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

        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = undefined;
        }

        router.get(
            routePath,
            { per_page: perPage.value },
            { preserveState: true },
        );
    }

    watch(
        filters,
        (nextFilters) => {
            syncFilterState(nextFilters);
        },
        { deep: true, immediate: true },
    );

    watch(search, () => {
        if (isSyncingFilters) {
            return;
        }

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(applyFilters, 400);
    });

    onBeforeUnmount(() => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
    });

    function openCreate() {
        editingTransaction.value = null;
        showForm.value = true;
    }

    function openEdit(transaction: Transaction) {
        editingTransaction.value = transaction;
        showForm.value = true;
    }

    function onSaved() {
        showForm.value = false;
        router.reload();
    }

    async function handleDelete() {
        if (!deleteTarget.value) {
            return;
        }

        try {
            await deleteTransaction(deleteTarget.value.id);
            success(deleteSuccessMessage);
            deleteTarget.value = null;
            router.reload();
        } catch {
            showError(deleteErrorMessage);
        }
    }

    function goToPage(page: number) {
        router.get(
            routePath,
            { ...buildQuery(), page: String(page) },
            { preserveState: true, preserveScroll: true },
        );
    }

    function setPerPage(value: string) {
        perPage.value = value;

        router.get(routePath, buildQuery(), {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function exportPdf() {
        const params = new URLSearchParams();
        params.set('type', exportType);

        Object.entries(buildQuery()).forEach(([key, value]) => {
            params.set(key, value);
        });

        exportingPdf.value = true;

        fetch(`/api/v1/export/transactions/pdf?${params.toString()}`, {
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Export failed');
                }

                return response.blob();
            })
            .then((blob) => {
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `${exportFilenamePrefix}_${new Date().toISOString().slice(0, 10)}.pdf`;
                link.click();
                URL.revokeObjectURL(link.href);
            })
            .catch(() => showError(exportErrorMessage))
            .finally(() => {
                exportingPdf.value = false;
            });
    }

    return {
        transactions,
        pagination,
        visibleAmountTotal,
        convertFromBase,
        search,
        showFilters,
        activeFiltersCount,
        categoryFilterSelectValue,
        paymentMethodFilterSelectValue,
        dateFrom,
        dateTo,
        perPage,
        showForm,
        editingTransaction,
        deleteTarget,
        exportingPdf,
        applyFilters,
        clearFilters,
        openCreate,
        openEdit,
        onSaved,
        handleDelete,
        goToPage,
        setPerPage,
        exportPdf,
    };
}
