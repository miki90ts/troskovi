<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BudgetFormDialog from '@/components/budgets/BudgetFormDialog.vue';
import BudgetsHeroSection from '@/components/budgets/BudgetsHeroSection.vue';
import BudgetsInsightsSection from '@/components/budgets/BudgetsInsightsSection.vue';
import BudgetsPeriodTabs from '@/components/budgets/BudgetsPeriodTabs.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { OVERALL_SENTINEL, useBudgetsPage } from '@/composables/useBudgetsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('settings.budgets.head'),
        href: '/settings/budgets',
    },
];

const {
    periodOptions,
    loading,
    hasTargets,
    categories,
    showForm,
    formSubmitting,
    editingTarget,
    deleteCandidate,
    statusFilter,
    activePeriodTab,
    form,
    totalCount,
    activeCount,
    categoryCount,
    atRiskCount,
    statusFilterOptions,
    filteredCount,
    topRiskTargets,
    periodSections,
    openCreate,
    openEdit,
    submitForm,
    toggleTarget,
    confirmDelete,
    updateForm,
} = useBudgetsPage();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="t('settings.budgets.head')" />
        <ToastContainer />

        <SettingsLayout>
            <BudgetsHeroSection
                :total-count="totalCount"
                :active-count="activeCount"
                :category-count="categoryCount"
                :at-risk-count="atRiskCount"
            />

            <section
                class="space-y-4 rounded-[1.75rem] border border-border/70 bg-card/95 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8"
            >
                <BudgetsInsightsSection
                    :top-risk-targets="topRiskTargets"
                    :status-filter-options="statusFilterOptions"
                    :status-filter="statusFilter"
                    :filtered-count="filteredCount"
                    @update:status-filter="statusFilter = $event"
                />

                <BudgetsPeriodTabs
                    :loading="loading"
                    :has-targets="hasTargets"
                    :active-period-tab="activePeriodTab"
                    :period-sections="periodSections"
                    :status-filter="statusFilter"
                    @update:active-period-tab="activePeriodTab = $event"
                    @create="openCreate($event)"
                    @edit="openEdit($event)"
                    @toggle="toggleTarget($event)"
                    @delete="deleteCandidate = $event"
                />
            </section>
        </SettingsLayout>

        <BudgetFormDialog
            :open="showForm"
            :form-submitting="formSubmitting"
            :editing-target="editingTarget"
            :categories="categories"
            :period-options="periodOptions"
            :form="form"
            :overall-sentinel="OVERALL_SENTINEL"
            @update:open="showForm = $event"
            @update:form="updateForm"
            @submit="submitForm"
        />

        <ConfirmDialog
            :open="!!deleteCandidate"
            :title="t('settings.budgets.deleteTitle')"
            :description="t('settings.budgets.deleteDescription')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @cancel="deleteCandidate = null"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
