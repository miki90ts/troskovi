import { router } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useRecurringTransactions } from '@/composables/useRecurringTransactions';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type { PaginationMeta } from '@/types/api';
import type { RecurringTransaction } from '@/types/models';

export type RecurringTransactionsPage = {
    data: RecurringTransaction[];
    meta: PaginationMeta;
};

export type RecurringListingFilters = Record<string, string | undefined>;
export type RecurringTransactionTab = 'expense' | 'income';
type PendingRecurringAction = 'deactivate' | 'delete';

export const ALL_RECURRING_PAYMENT_METHODS_VALUE = '__all_payment_methods__';
export const ALL_RECURRING_FREQUENCIES_VALUE = '__all_frequencies__';
export const ALL_RECURRING_STATUSES_VALUE = '__all_statuses__';
export const DEFAULT_RECURRING_PER_PAGE = '15';

export function useRecurringTransactionsPage(options: {
    recurringTransactionsPage: Ref<RecurringTransactionsPage>;
    filters: Ref<RecurringListingFilters>;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
}) {
    const {
        recurringTransactionsPage,
        filters,
        defaultCurrencyCode,
        latestExchangeRates,
    } = options;
    const { success, error: showError } = useToast();
    const { deleteRecurring, updateRecurring } = useRecurringTransactions();

    const activeTab = ref<RecurringTransactionTab>('expense');
    const search = ref('');
    const categoryFilter = ref('');
    const paymentMethodFilter = ref('');
    const frequencyFilter = ref('');
    const statusFilter = ref('');
    const dateFrom = ref('');
    const dateTo = ref('');
    const perPage = ref(DEFAULT_RECURRING_PER_PAGE);
    const showFilters = ref(false);
    const showForm = ref(false);
    const editingRecurring = ref<RecurringTransaction | null>(null);
    const actionTarget = ref<RecurringTransaction | null>(null);
    const pendingAction = ref<PendingRecurringAction | null>(null);

    let isSyncingFilters = false;
    let searchTimeout: ReturnType<typeof setTimeout> | undefined;

    const recurringTransactions = computed(
        () => recurringTransactionsPage.value.data,
    );
    const pagination = computed(() => recurringTransactionsPage.value.meta);
    const activeTabDisplay = computed(() =>
        activeTab.value === 'expense'
            ? t('finance.recurring.expenseTitle')
            : t('finance.recurring.incomeTitle'),
    );

    function resolveRate(currencyCode: string): number | null {
        if (currencyCode === 'RSD') {
            return 1;
        }

        const rate = latestExchangeRates[currencyCode];

        return typeof rate === 'number' && rate > 0 ? rate : null;
    }

    function convertToDefault(item: RecurringTransaction): number {
        const sourceCode = item.currency?.iso_code ?? defaultCurrencyCode;
        const sourceRate = resolveRate(sourceCode);
        const targetRate = resolveRate(defaultCurrencyCode);

        if (!sourceRate || !targetRate) {
            return item.amount;
        }

        return Number(((item.amount * sourceRate) / targetRate).toFixed(2));
    }

    const visibleAmountTotal = computed(() =>
        recurringTransactions.value.reduce(
            (sum, item) => sum + convertToDefault(item),
            0,
        ),
    );
    const activeFiltersCount = computed(
        () =>
            [
                search.value,
                categoryFilter.value,
                paymentMethodFilter.value,
                frequencyFilter.value,
                statusFilter.value,
                dateFrom.value,
                dateTo.value,
            ].filter(Boolean).length,
    );

    const paymentMethodSelectValue = computed({
        get: () =>
            paymentMethodFilter.value || ALL_RECURRING_PAYMENT_METHODS_VALUE,
        set: (value: string) => {
            paymentMethodFilter.value =
                value === ALL_RECURRING_PAYMENT_METHODS_VALUE ? '' : value;
        },
    });
    const frequencySelectValue = computed({
        get: () => frequencyFilter.value || ALL_RECURRING_FREQUENCIES_VALUE,
        set: (value: string) => {
            frequencyFilter.value =
                value === ALL_RECURRING_FREQUENCIES_VALUE ? '' : value;
        },
    });
    const statusSelectValue = computed({
        get: () => statusFilter.value || ALL_RECURRING_STATUSES_VALUE,
        set: (value: string) => {
            statusFilter.value =
                value === ALL_RECURRING_STATUSES_VALUE ? '' : value;
        },
    });

    const confirmDialogTitle = computed(() =>
        pendingAction.value === 'delete'
            ? t('finance.recurring.deleteTitle')
            : t('finance.recurring.deactivateTitle'),
    );
    const confirmDialogDescription = computed(() =>
        pendingAction.value === 'delete'
            ? t('finance.recurring.deleteDescription')
            : t('finance.recurring.deactivateDescription'),
    );
    const confirmDialogConfirmText = computed(() =>
        pendingAction.value === 'delete'
            ? t('finance.recurring.deleteConfirm')
            : t('finance.recurring.deactivateConfirm'),
    );

    function syncFilterState(nextFilters: RecurringListingFilters) {
        isSyncingFilters = true;

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        activeTab.value = nextFilters.type === 'income' ? 'income' : 'expense';
        search.value = nextFilters.search ?? '';
        categoryFilter.value =
            nextFilters.category_ids ?? nextFilters.category_id ?? '';
        paymentMethodFilter.value = nextFilters.payment_method ?? '';
        frequencyFilter.value = nextFilters.frequency ?? '';
        statusFilter.value = nextFilters.status ?? '';
        dateFrom.value = nextFilters.date_from ?? '';
        dateTo.value = nextFilters.date_to ?? '';
        perPage.value = nextFilters.per_page ?? DEFAULT_RECURRING_PER_PAGE;

        void nextTick(() => {
            isSyncingFilters = false;
        });
    }

    function buildQuery(): Record<string, string> {
        const query: Record<string, string> = {
            type: activeTab.value,
            per_page: perPage.value,
        };

        if (search.value) {
            query.search = search.value;
        }

        if (categoryFilter.value) {
            query.category_ids = categoryFilter.value;
        }

        if (paymentMethodFilter.value) {
            query.payment_method = paymentMethodFilter.value;
        }

        if (frequencyFilter.value) {
            query.frequency = frequencyFilter.value;
        }

        if (statusFilter.value) {
            query.status = statusFilter.value;
        }

        if (dateFrom.value) {
            query.date_from = dateFrom.value;
        }

        if (dateTo.value) {
            query.date_to = dateTo.value;
        }

        return query;
    }

    function applyFilters() {
        router.get('/recurring-transactions', buildQuery(), {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function clearFilters() {
        search.value = '';
        categoryFilter.value = '';
        paymentMethodFilter.value = '';
        frequencyFilter.value = '';
        statusFilter.value = '';
        dateFrom.value = '';
        dateTo.value = '';

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        applyFilters();
    }

    function goToPage(page: number) {
        router.get(
            '/recurring-transactions',
            { ...buildQuery(), page: String(page) },
            { preserveState: true, preserveScroll: true },
        );
    }

    function setPerPage(value: string) {
        perPage.value = value;
        applyFilters();
    }

    watch(filters, syncFilterState, { deep: true, immediate: true });
    watch(activeTab, () => {
        if (!isSyncingFilters) {
            categoryFilter.value = '';
            applyFilters();
        }
    });
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
        editingRecurring.value = null;
        showForm.value = true;
    }

    function openEdit(item: RecurringTransaction) {
        editingRecurring.value = item;
        showForm.value = true;
    }

    function closeForm() {
        showForm.value = false;
        editingRecurring.value = null;
    }

    function onSaved() {
        closeForm();
        router.reload({ only: ['recurringTransactions', 'counts'] });
    }

    function requestDeactivate(item: RecurringTransaction) {
        actionTarget.value = item;
        pendingAction.value = 'deactivate';
    }

    function requestDelete(item: RecurringTransaction) {
        actionTarget.value = item;
        pendingAction.value = 'delete';
    }

    function clearPendingAction() {
        actionTarget.value = null;
        pendingAction.value = null;
    }

    async function handleActivate(item: RecurringTransaction) {
        try {
            await updateRecurring(item.id, { is_active: true });
            success(t('finance.recurring.activated'));
            router.reload({ only: ['recurringTransactions'] });
        } catch {
            showError(t('finance.recurring.activateError'));
        }
    }

    async function handleConfirmedAction() {
        if (!actionTarget.value || !pendingAction.value) {
            return;
        }

        try {
            if (pendingAction.value === 'delete') {
                await deleteRecurring(actionTarget.value.id);
                success(t('finance.recurring.deleted'));
            } else {
                await updateRecurring(actionTarget.value.id, {
                    is_active: false,
                });
                success(t('finance.recurring.deactivated'));
            }

            clearPendingAction();
            router.reload({ only: ['recurringTransactions', 'counts'] });
        } catch {
            showError(
                t(
                    pendingAction.value === 'delete'
                        ? 'finance.recurring.deleteError'
                        : 'finance.recurring.deactivateError',
                ),
            );
        }
    }

    return {
        activeTab,
        recurringTransactions,
        pagination,
        activeTabDisplay,
        visibleAmountTotal,
        search,
        categoryFilter,
        paymentMethodSelectValue,
        frequencySelectValue,
        statusSelectValue,
        dateFrom,
        dateTo,
        perPage,
        showFilters,
        activeFiltersCount,
        showForm,
        editingRecurring,
        actionTarget,
        pendingAction,
        confirmDialogTitle,
        confirmDialogDescription,
        confirmDialogConfirmText,
        applyFilters,
        clearFilters,
        goToPage,
        setPerPage,
        openCreate,
        openEdit,
        closeForm,
        onSaved,
        requestDeactivate,
        requestDelete,
        clearPendingAction,
        handleActivate,
        handleConfirmedAction,
    };
}
