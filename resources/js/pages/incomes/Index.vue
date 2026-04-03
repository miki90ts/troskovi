<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { toRef } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import TransactionFormDialog from '@/components/TransactionFormDialog.vue';
import IncomeHeroSection from '@/components/incomes/IncomeHeroSection.vue';
import IncomesManagementSection from '@/components/incomes/IncomesManagementSection.vue';
import {
    ALL_CATEGORIES_VALUE,
    ALL_PAYMENT_METHODS_VALUE,
    useIncomesPage,
} from '@/composables/useIncomesPage';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { PaginationMeta } from '@/types/api';
import type { Category, Transaction } from '@/types/models';

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

const {
    transactions,
    pagination,
    visibleAmountTotal,
    averageIncome,
    accountLinkedCount,
    search,
    showFilters,
    activeFiltersCount,
    categoryFilterSelectValue,
    paymentMethodFilterSelectValue,
    dateFrom,
    dateTo,
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
    exportPdf,
} = useIncomesPage({
    transactionsPage: toRef(props, 'transactions'),
    filters: toRef(props, 'filters'),
});
</script>

<template>
    <Head :title="t('finance.incomes.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <IncomeHeroSection
                :visible-amount-total="visibleAmountTotal"
                :average-income="averageIncome"
                :account-linked-count="accountLinkedCount"
            />

            <IncomesManagementSection
                :categories="categories.data"
                :transactions="transactions"
                :pagination="pagination"
                :accounts-count="accounts.length"
                :search="search"
                :show-filters="showFilters"
                :active-filters-count="activeFiltersCount"
                :category-filter-value="categoryFilterSelectValue"
                :payment-method-filter-value="paymentMethodFilterSelectValue"
                :date-from="dateFrom"
                :date-to="dateTo"
                :all-categories-value="ALL_CATEGORIES_VALUE"
                :all-payment-methods-value="ALL_PAYMENT_METHODS_VALUE"
                :exporting-pdf="exportingPdf"
                @update:search="search = $event"
                @update:show-filters="showFilters = $event"
                @update:category-filter-value="
                    categoryFilterSelectValue = $event
                "
                @update:payment-method-filter-value="
                    paymentMethodFilterSelectValue = $event
                "
                @update:date-from="dateFrom = $event"
                @update:date-to="dateTo = $event"
                @apply-filters="applyFilters"
                @clear-filters="clearFilters"
                @export="exportPdf"
                @create="openCreate"
                @edit="openEdit"
                @delete="deleteTarget = $event"
                @page-change="goToPage"
            />
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
