<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoriesHeroSection from '@/components/categories/CategoriesHeroSection.vue';
import CategoriesManagementSection from '@/components/categories/CategoriesManagementSection.vue';
import CategoryFormDialog from '@/components/categories/CategoryFormDialog.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useCategoriesPage } from '@/composables/useCategoriesPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { Category } from '@/types/models';

const props = defineProps<{
    categories: { data: Category[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.categories'), href: '/categories' },
];

const {
    categories,
    activeTab,
    expenseCategories,
    incomeCategories,
    systemCount,
    customCount,
    colorPresets,
    showForm,
    editingCategory,
    formSubmitting,
    categoryForm,
    deleteTarget,
    openCreate,
    openEdit,
    applyPresetColor,
    submitForm,
    handleDelete,
} = useCategoriesPage(props.categories.data);
</script>

<template>
    <Head :title="t('finance.categories.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <CategoriesHeroSection
                :total-categories="categories.length"
                :expense-count="expenseCategories.length"
                :income-count="incomeCategories.length"
                :active-tab="activeTab"
                :system-count="systemCount"
                :custom-count="customCount"
            />

            <CategoriesManagementSection
                :active-tab="activeTab"
                :expense-categories="expenseCategories"
                :income-categories="incomeCategories"
                @update:active-tab="activeTab = $event"
                @create="openCreate"
                @edit="openEdit"
                @delete="deleteTarget = $event"
            />
        </div>

        <CategoryFormDialog
            :open="showForm"
            :editing-category="editingCategory"
            :form-submitting="formSubmitting"
            :color-presets="colorPresets"
            :form="categoryForm"
            @update:open="(value) => (showForm = value)"
            @update:form="(value) => (categoryForm = value)"
            @submit="submitForm"
            @close="showForm = false"
            @apply-preset-color="applyPresetColor"
        />

        <ConfirmDialog
            :open="!!deleteTarget"
            :title="t('finance.categories.deleteTitle')"
            :description="t('finance.categories.deleteDescription')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
