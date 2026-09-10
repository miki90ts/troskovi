import { router } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { Ref } from 'vue';

export type WarrantyListingFilters = Record<string, string | undefined>;

export const ALL_WARRANTY_STATUSES_VALUE = '__all_warranty_statuses__';
export const ALL_PAYMENT_METHODS_VALUE = '__all_payment_methods__';
export const DEFAULT_PER_PAGE = '15';

export function useWarrantiesPage(options: {
    filters: Ref<WarrantyListingFilters>;
}) {
    const { filters } = options;

    const search = ref('');
    const statusFilter = ref('');
    const categoryFilter = ref('');
    const paymentMethodFilter = ref('');
    const dateFrom = ref('');
    const dateTo = ref('');
    const perPage = ref(DEFAULT_PER_PAGE);
    const showFilters = ref(false);

    let isSyncingFilters = false;
    let searchTimeout: ReturnType<typeof setTimeout> | undefined;

    const activeFiltersCount = computed(
        () =>
            [
                search.value,
                statusFilter.value,
                categoryFilter.value,
                paymentMethodFilter.value,
                dateFrom.value,
                dateTo.value,
            ].filter(Boolean).length,
    );

    const statusSelectValue = computed({
        get: () => statusFilter.value || ALL_WARRANTY_STATUSES_VALUE,
        set: (value: string) => {
            statusFilter.value =
                value === ALL_WARRANTY_STATUSES_VALUE ? '' : value;
        },
    });

    const paymentMethodSelectValue = computed({
        get: () => paymentMethodFilter.value || ALL_PAYMENT_METHODS_VALUE,
        set: (value: string) => {
            paymentMethodFilter.value =
                value === ALL_PAYMENT_METHODS_VALUE ? '' : value;
        },
    });

    function syncFilterState(nextFilters: WarrantyListingFilters) {
        isSyncingFilters = true;

        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = undefined;
        }

        search.value = nextFilters.search ?? '';
        statusFilter.value = nextFilters.status ?? '';
        categoryFilter.value =
            nextFilters.category_ids ?? nextFilters.category_id ?? '';
        paymentMethodFilter.value = nextFilters.payment_method ?? '';
        dateFrom.value = nextFilters.date_from ?? '';
        dateTo.value = nextFilters.date_to ?? '';
        perPage.value = nextFilters.per_page ?? DEFAULT_PER_PAGE;

        void nextTick(() => {
            isSyncingFilters = false;
        });
    }

    function buildQuery(): Record<string, string> {
        const query: Record<string, string> = {};

        if (search.value) {
            query.search = search.value;
        }

        if (statusFilter.value) {
            query.status = statusFilter.value;
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
        router.get('/warranties', buildQuery(), {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function clearFilters() {
        search.value = '';
        statusFilter.value = '';
        categoryFilter.value = '';
        paymentMethodFilter.value = '';
        dateFrom.value = '';
        dateTo.value = '';

        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = undefined;
        }

        router.get(
            '/warranties',
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

    function goToPage(page: number) {
        router.get(
            '/warranties',
            { ...buildQuery(), page: String(page) },
            { preserveState: true, preserveScroll: true },
        );
    }

    function setPerPage(value: string) {
        perPage.value = value;
        applyFilters();
    }

    return {
        search,
        statusSelectValue,
        categoryFilter,
        paymentMethodSelectValue,
        dateFrom,
        dateTo,
        perPage,
        showFilters,
        activeFiltersCount,
        applyFilters,
        clearFilters,
        goToPage,
        setPerPage,
    };
}
