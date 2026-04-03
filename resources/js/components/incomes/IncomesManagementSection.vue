<script setup lang="ts">
import { t } from '@/lib/i18n';
import type { PaginationMeta } from '@/types/api';
import type { Category, Transaction } from '@/types/models';
import TransactionManagementShell from '@/components/transactions/TransactionManagementShell.vue';
import TransactionManagementToolbar from '@/components/transactions/TransactionManagementToolbar.vue';
import IncomeFiltersPanel from './IncomeFiltersPanel.vue';
import IncomesTransactionsTable from './IncomesTransactionsTable.vue';

const props = defineProps<{
    categories: Category[];
    transactions: Transaction[];
    pagination: PaginationMeta;
    accountsCount: number;
    search: string;
    showFilters: boolean;
    activeFiltersCount: number;
    categoryFilterValue: string;
    paymentMethodFilterValue: string;
    dateFrom: string;
    dateTo: string;
    allCategoriesValue: string;
    allPaymentMethodsValue: string;
    exportingPdf: boolean;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'update:showFilters': [value: boolean];
    'update:categoryFilterValue': [value: string];
    'update:paymentMethodFilterValue': [value: string];
    'update:dateFrom': [value: string];
    'update:dateTo': [value: string];
    applyFilters: [];
    clearFilters: [];
    export: [];
    create: [];
    edit: [transaction: Transaction];
    delete: [transaction: Transaction];
    pageChange: [page: number];
}>();
</script>

<template>
    <TransactionManagementShell
        :eyebrow="t('finance.incomes.recordsTitle')"
        :title="t('finance.incomes.recordsDescription')"
    >
        <template #header-actions>
            <TransactionManagementToolbar
                :search="search"
                :search-placeholder="t('finance.incomes.searchPlaceholder')"
                :show-filters="showFilters"
                :active-filters-count="activeFiltersCount"
                :exporting-pdf="exportingPdf"
                :create-label="t('finance.incomes.add')"
                @update:search="emit('update:search', $event)"
                @toggle-filters="emit('update:showFilters', !showFilters)"
                @export="emit('export')"
                @create="emit('create')"
            />
        </template>

        <template #filters>
            <IncomeFiltersPanel
                v-if="showFilters"
                :categories="categories"
                :category-filter-value="categoryFilterValue"
                :payment-method-filter-value="paymentMethodFilterValue"
                :date-from="dateFrom"
                :date-to="dateTo"
                :all-categories-value="allCategoriesValue"
                :all-payment-methods-value="allPaymentMethodsValue"
                @update:category-filter-value="
                    emit('update:categoryFilterValue', $event)
                "
                @update:payment-method-filter-value="
                    emit('update:paymentMethodFilterValue', $event)
                "
                @update:date-from="emit('update:dateFrom', $event)"
                @update:date-to="emit('update:dateTo', $event)"
                @apply-filters="emit('applyFilters')"
                @clear-filters="emit('clearFilters')"
            />
        </template>

        <template #default>
            <IncomesTransactionsTable
                :transactions="transactions"
                :pagination="pagination"
                :accounts-count="accountsCount"
                @create="emit('create')"
                @edit="emit('edit', $event)"
                @delete="emit('delete', $event)"
                @page-change="emit('pageChange', $event)"
            />
        </template>
    </TransactionManagementShell>
</template>
