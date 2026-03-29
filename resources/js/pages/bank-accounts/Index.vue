<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BankAccountFormDialog from '@/components/bank-accounts/BankAccountFormDialog.vue';
import BankAccountsHeroSection from '@/components/bank-accounts/BankAccountsHeroSection.vue';
import BankAccountsOverviewSection from '@/components/bank-accounts/BankAccountsOverviewSection.vue';
import BankAccountTransferDialog from '@/components/bank-accounts/BankAccountTransferDialog.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useBankAccountsPage } from '@/composables/useBankAccountsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    accounts: { data: BankAccount[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.bankAccounts'), href: '/bank-accounts' },
];

const {
    activeAccounts,
    archivedAccounts,
    totalBalance,
    connectedBanks,
    colorPresets,
    showForm,
    editingAccount,
    formSubmitting,
    accountForm,
    archiveConfirm,
    showTransfer,
    transferSubmitting,
    transferForm,
    openCreate,
    openEdit,
    applyPresetColor,
    openTransfer,
    submitForm,
    handleArchive,
    handleRestore,
    submitTransfer,
} = useBankAccountsPage(props.accounts.data);
</script>

<template>
    <Head :title="t('finance.bankAccounts.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <BankAccountsHeroSection
                :active-count="activeAccounts.length"
                :archived-count="archivedAccounts.length"
                :total-balance="totalBalance"
                :connected-banks="connectedBanks"
                @create="openCreate"
                @transfer="openTransfer"
            />

            <BankAccountsOverviewSection
                :active-accounts="activeAccounts"
                :archived-accounts="archivedAccounts"
                :connected-banks="connectedBanks"
                @create="openCreate"
                @edit="openEdit"
                @archive="archiveConfirm = $event"
                @restore="handleRestore"
            />
        </div>

        <BankAccountFormDialog
            :open="showForm"
            :editing-account="editingAccount"
            :form-submitting="formSubmitting"
            :color-presets="colorPresets"
            :form="accountForm"
            @update:open="(value) => (showForm = value)"
            @update:form="(value) => (accountForm = value)"
            @submit="submitForm"
            @close="showForm = false"
            @apply-preset-color="applyPresetColor"
        />

        <BankAccountTransferDialog
            :open="showTransfer"
            :submitting="transferSubmitting"
            :active-accounts="activeAccounts"
            :form="transferForm"
            @update:open="(value) => (showTransfer = value)"
            @update:form="(value) => (transferForm = value)"
            @submit="submitTransfer"
            @close="showTransfer = false"
        />

        <ConfirmDialog
            :open="!!archiveConfirm"
            :title="t('finance.bankAccounts.archiveTitle')"
            :description="t('finance.bankAccounts.archiveDescription')"
            :confirm-text="t('finance.bankAccounts.archiveMenu')"
            destructive
            @confirm="handleArchive"
            @cancel="archiveConfirm = null"
        />
    </AppLayout>
</template>
