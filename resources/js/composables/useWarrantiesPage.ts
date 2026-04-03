import { router } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch, type Ref } from 'vue';

export type WarrantyListingFilters = Record<string, string | undefined>;

export const ALL_WARRANTY_STATUSES_VALUE = '__all_warranty_statuses__';

export function useWarrantiesPage(options: {
    filters: Ref<WarrantyListingFilters>;
}) {
    const { filters } = options;

    const search = ref('');
    const statusFilter = ref('');

    let isSyncingFilters = false;
    let searchTimeout: ReturnType<typeof setTimeout> | undefined;

    const activeFiltersCount = computed(
        () => [search.value, statusFilter.value].filter(Boolean).length,
    );

    const statusSelectValue = computed({
        get: () => statusFilter.value || ALL_WARRANTY_STATUSES_VALUE,
        set: (value: string) => {
            statusFilter.value =
                value === ALL_WARRANTY_STATUSES_VALUE ? '' : value;
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

        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = undefined;
        }

        router.get('/warranties', {}, { preserveState: true });
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

    watch(statusFilter, () => {
        if (isSyncingFilters) {
            return;
        }

        applyFilters();
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

    return {
        search,
        statusSelectValue,
        activeFiltersCount,
        applyFilters,
        clearFilters,
        goToPage,
    };
}
