<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { toRef } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import RecurringTransactionsHeroSection from '@/components/recurring-transactions/RecurringTransactionsHeroSection.vue';
import RecurringTransactionsManagementSection from '@/components/recurring-transactions/RecurringTransactionsManagementSection.vue';
import RecurringTransactionFormDialog from '@/components/RecurringTransactionFormDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import {
    ALL_RECURRING_FREQUENCIES_VALUE,
    ALL_RECURRING_PAYMENT_METHODS_VALUE,
    ALL_RECURRING_STATUSES_VALUE,
    useRecurringTransactionsPage,
} from '@/composables/useRecurringTransactionsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { PaginationMeta } from '@/types/api';
import type {
    Category,
    CurrencySummary,
    Debt,
    RecurringTransaction,
} from '@/types/models';

const props = defineProps<{
    recurringTransactions: {
        data: RecurringTransaction[];
        meta: PaginationMeta;
    };
    counts: { expense: number; income: number };
    categories: { data: Category[] };
    accounts: {
        id: number;
        name: string;
        currency: string;
        currency_id: number | null;
    }[];
    debts: Debt[];
    filters: Record<string, string | undefined>;
    defaultCurrency: CurrencySummary;
    latestExchangeRates: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('finance.recurring.head'), href: '/recurring-transactions' },
];

const {
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
    openCreate,
    openEdit,
    closeForm,
    onSaved,
    requestDeactivate,
    requestDelete,
    clearPendingAction,
    handleActivate,
    handleConfirmedAction,
    applyFilters,
    clearFilters,
    goToPage,
    setPerPage,
} = useRecurringTransactionsPage({
    recurringTransactionsPage: toRef(props, 'recurringTransactions'),
    filters: toRef(props, 'filters'),
    defaultCurrencyCode: props.defaultCurrency.iso_code,
    latestExchangeRates: props.latestExchangeRates,
});
</script>

<template>
    <Head :title="t('finance.recurring.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <RecurringTransactionsHeroSection
                :expense-count="counts.expense"
                :income-count="counts.income"
                :visible-amount-total="visibleAmountTotal"
                :active-tab-display="activeTabDisplay"
                :default-currency-code="defaultCurrency.iso_code"
            />

            <RecurringTransactionsManagementSection
                :active-tab="activeTab"
                :transactions="recurringTransactions"
                :pagination="pagination"
                :categories="categories.data"
                :accounts-count="accounts.length"
                :search="search"
                :show-filters="showFilters"
                :active-filters-count="activeFiltersCount"
                :category-filter-value="categoryFilter"
                :payment-method-filter-value="paymentMethodSelectValue"
                :frequency-filter-value="frequencySelectValue"
                :status-filter-value="statusSelectValue"
                :date-from="dateFrom"
                :date-to="dateTo"
                :per-page="perPage"
                :default-currency-code="defaultCurrency.iso_code"
                :latest-exchange-rates="latestExchangeRates"
                :all-payment-methods-value="ALL_RECURRING_PAYMENT_METHODS_VALUE"
                :all-frequencies-value="ALL_RECURRING_FREQUENCIES_VALUE"
                :all-statuses-value="ALL_RECURRING_STATUSES_VALUE"
                @update:active-tab="activeTab = $event"
                @update:search="search = $event"
                @update:show-filters="showFilters = $event"
                @update:category-filter-value="categoryFilter = $event"
                @update:payment-method-filter-value="
                    paymentMethodSelectValue = $event
                "
                @update:frequency-filter-value="frequencySelectValue = $event"
                @update:status-filter-value="statusSelectValue = $event"
                @update:date-from="dateFrom = $event"
                @update:date-to="dateTo = $event"
                @apply-filters="applyFilters"
                @clear-filters="clearFilters"
                @page-change="goToPage"
                @per-page-change="setPerPage"
                @create="openCreate"
                @edit="openEdit"
                @activate="handleActivate"
                @deactivate="requestDeactivate"
                @delete="requestDelete"
            />
        </div>

        <RecurringTransactionFormDialog
            :open="showForm"
            :recurring-transaction="editingRecurring"
            :categories="categories.data"
            :accounts="accounts"
            :debts="debts"
            :default-type="activeTab"
            @close="closeForm"
            @saved="onSaved"
        />

        <ConfirmDialog
            :open="!!pendingAction && !!actionTarget"
            :title="confirmDialogTitle"
            :description="confirmDialogDescription"
            :confirm-text="confirmDialogConfirmText"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @confirm="handleConfirmedAction"
            @cancel="clearPendingAction"
        />
    </AppLayout>
</template>
