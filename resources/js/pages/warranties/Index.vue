<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { toRef } from 'vue';
import WarrantiesHeroSection from '@/components/warranties/WarrantiesHeroSection.vue';
import WarrantiesManagementSection from '@/components/warranties/WarrantiesManagementSection.vue';
import {
    ALL_PAYMENT_METHODS_VALUE,
    ALL_WARRANTY_STATUSES_VALUE,
    useWarrantiesPage,
} from '@/composables/useWarrantiesPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { PaginationMeta } from '@/types/api';
import type { Category, CurrencySummary, Transaction } from '@/types/models';

const props = defineProps<{
    transactions: { data: Transaction[]; meta: PaginationMeta };
    counts: { active: number; expiring_soon: number; expired: number };
    categories: { data: Category[] };
    filters: Record<string, string | undefined>;
    defaultCurrency: CurrencySummary;
    latestExchangeRates: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.warranties'), href: '/warranties' },
];

const {
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
} = useWarrantiesPage({
    filters: toRef(props, 'filters'),
});
</script>

<template>
    <Head :title="t('finance.warranties.head')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <WarrantiesHeroSection :counts="counts" />

            <WarrantiesManagementSection
                :transactions="transactions.data"
                :categories="categories.data"
                :pagination="transactions.meta"
                :search="search"
                :show-filters="showFilters"
                :active-filters-count="activeFiltersCount"
                :category-filter-value="categoryFilter"
                :payment-method-filter-value="paymentMethodSelectValue"
                :status-filter-value="statusSelectValue"
                :date-from="dateFrom"
                :date-to="dateTo"
                :per-page="perPage"
                :default-currency-code="defaultCurrency.iso_code"
                :latest-exchange-rates="latestExchangeRates"
                :all-payment-methods-value="ALL_PAYMENT_METHODS_VALUE"
                :all-statuses-value="ALL_WARRANTY_STATUSES_VALUE"
                @update:search="search = $event"
                @update:show-filters="showFilters = $event"
                @update:category-filter-value="categoryFilter = $event"
                @update:payment-method-filter-value="
                    paymentMethodSelectValue = $event
                "
                @update:status-filter-value="statusSelectValue = $event"
                @update:date-from="dateFrom = $event"
                @update:date-to="dateTo = $event"
                @apply-filters="applyFilters"
                @clear-filters="clearFilters"
                @page-change="goToPage"
                @per-page-change="setPerPage"
            />
        </div>
    </AppLayout>
</template>
