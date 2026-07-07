<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DebtFormDialog from '@/components/debts/DebtFormDialog.vue';
import DebtsHeroSection from '@/components/debts/DebtsHeroSection.vue';
import DebtsManagementSection from '@/components/debts/DebtsManagementSection.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useDebtsPage } from '@/composables/useDebtsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { CurrencySummary, Debt, DebtSummary } from '@/types/models';

const props = defineProps<{
    debts: { data: Debt[] };
    summary: DebtSummary;
    currencies: CurrencySummary[];
    defaultCurrencyId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.debts'), href: '/debts' },
];

const {
    summary,
    activeTab,
    iOweDebts,
    owedToMeDebts,
    filteredDebts,
    activeTabTotal,
    showForm,
    editingDebt,
    formSubmitting,
    debtForm,
    formErrors,
    deleteTarget,
    searchQuery,
    statusFilter,
    openCreate,
    openEdit,
    setDebtForm,
    closeForm,
    submitForm,
    handleDelete,
    handleSettle,
} = useDebtsPage(
    props.debts.data,
    props.summary,
    props.currencies,
    props.defaultCurrencyId,
);
</script>

<template>
    <Head :title="t('debts.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <DebtsHeroSection :summary="summary" @create="openCreate" />

            <DebtsManagementSection
                :active-tab="activeTab"
                :filtered-debts="filteredDebts"
                :i-owe-debts="iOweDebts"
                :owed-to-me-debts="owedToMeDebts"
                :active-tab-total="activeTabTotal"
                :search-query="searchQuery"
                :status-filter="statusFilter"
                @update:active-tab="activeTab = $event"
                @update:search-query="searchQuery = $event"
                @update:status-filter="statusFilter = $event"
                @create="openCreate"
                @edit="openEdit"
                @delete="deleteTarget = $event"
                @settle="handleSettle"
            />
        </div>

        <DebtFormDialog
            :open="showForm"
            :editing-debt="editingDebt"
            :form-submitting="formSubmitting"
            :currencies="props.currencies"
            :form="debtForm"
            :errors="formErrors"
            @update:open="(value) => (showForm = value)"
            @update:form="setDebtForm"
            @submit="submitForm"
            @close="closeForm"
        />

        <ConfirmDialog
            :open="!!deleteTarget"
            :title="t('debts.deleteTitle')"
            :description="t('debts.deleteDescription')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
