<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { toRef } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import RecurringTransactionFormDialog from '@/components/RecurringTransactionFormDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import RecurringTransactionsHeroSection from '@/components/recurring-transactions/RecurringTransactionsHeroSection.vue';
import RecurringTransactionsManagementSection from '@/components/recurring-transactions/RecurringTransactionsManagementSection.vue';
import { useRecurringTransactionsPage } from '@/composables/useRecurringTransactionsPage';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { Category, RecurringTransaction } from '@/types/models';

const props = defineProps<{
    recurringTransactions: { data: RecurringTransaction[] };
    categories: { data: Category[] };
    accounts: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('finance.recurring.head'), href: '/recurring-transactions' },
];

const {
    activeTab,
    expenseRecurring,
    incomeRecurring,
    activeTabDisplay,
    visibleAmountTotal,
    showForm,
    editingRecurring,
    deactivateTarget,
    openCreate,
    openEdit,
    closeForm,
    onSaved,
    handleDeactivate,
} = useRecurringTransactionsPage({
    recurringTransactionsPage: toRef(props, 'recurringTransactions'),
});
</script>

<template>
    <Head :title="t('finance.recurring.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <RecurringTransactionsHeroSection
                :expense-count="expenseRecurring.length"
                :income-count="incomeRecurring.length"
                :visible-amount-total="visibleAmountTotal"
                :active-tab-display="activeTabDisplay"
            />

            <RecurringTransactionsManagementSection
                :active-tab="activeTab"
                :expense-transactions="expenseRecurring"
                :income-transactions="incomeRecurring"
                :accounts-count="accounts.length"
                @update:active-tab="activeTab = $event"
                @create="openCreate"
                @edit="openEdit"
                @deactivate="deactivateTarget = $event"
            />
        </div>

        <RecurringTransactionFormDialog
            :open="showForm"
            :recurring-transaction="editingRecurring"
            :categories="categories.data"
            :accounts="accounts"
            :default-type="activeTab"
            @close="closeForm"
            @saved="onSaved"
        />

        <ConfirmDialog
            :open="!!deactivateTarget"
            :title="t('finance.recurring.deactivateTitle')"
            :description="t('finance.recurring.deactivateDescription')"
            :confirm-text="t('finance.recurring.deactivateConfirm')"
            destructive
            @confirm="handleDeactivate"
            @cancel="deactivateTarget = null"
        />
    </AppLayout>
</template>
