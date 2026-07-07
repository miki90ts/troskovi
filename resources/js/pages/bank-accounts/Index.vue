<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BankAccountFormDialog from '@/components/bank-accounts/BankAccountFormDialog.vue';
import BankAccountTransfersHistorySection from '@/components/bank-accounts/BankAccountTransfersHistorySection.vue';
import BankAccountsHeroSection from '@/components/bank-accounts/BankAccountsHeroSection.vue';
import BankAccountsOverviewSection from '@/components/bank-accounts/BankAccountsOverviewSection.vue';
import BankAccountTransferDialog from '@/components/bank-accounts/BankAccountTransferDialog.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useBankAccountsPage } from '@/composables/useBankAccountsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type {
    AccountTransfer,
    BankAccount,
    CurrencySummary,
} from '@/types/models';

const props = defineProps<{
    accounts: { data: BankAccount[] };
    transfers: { data: AccountTransfer[] };
    currencies: CurrencySummary[];
    latestExchangeRates: Record<string, number>;
    defaultCurrencyId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.bankAccounts'), href: '/bank-accounts' },
];

const {
    activeAccounts,
    archivedAccounts,
    transfers,
    totalBalance,
    defaultCurrency,
    connectedBanks,
    colorPresets,
    showForm,
    editingAccount,
    formSubmitting,
    accountForm,
    formErrors,
    archiveConfirm,
    showTransfer,
    transferSubmitting,
    transferForm,
    transferErrors,
    transferPreview,
    openCreate,
    openEdit,
    setAccountForm,
    closeForm,
    applyPresetColor,
    openTransfer,
    setTransferForm,
    closeTransfer,
    submitForm,
    handleArchive,
    handleRestore,
    submitTransfer,
} = useBankAccountsPage(
    props.accounts.data,
    props.transfers.data,
    props.currencies,
    props.latestExchangeRates,
    props.defaultCurrencyId,
);
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
                :display-currency-code="defaultCurrency?.iso_code ?? 'RSD'"
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

            <BankAccountTransfersHistorySection :transfers="transfers" />
        </div>

        <BankAccountFormDialog
            :open="showForm"
            :editing-account="editingAccount"
            :form-submitting="formSubmitting"
            :color-presets="colorPresets"
            :currencies="props.currencies"
            :form="accountForm"
            :errors="formErrors"
            @update:open="(value) => (showForm = value)"
            @update:form="setAccountForm"
            @submit="submitForm"
            @close="closeForm"
            @apply-preset-color="applyPresetColor"
        />

        <BankAccountTransferDialog
            :open="showTransfer"
            :submitting="transferSubmitting"
            :active-accounts="activeAccounts"
            :transfer-preview="transferPreview"
            :form="transferForm"
            :errors="transferErrors"
            @update:open="(value) => (showTransfer = value)"
            @update:form="setTransferForm"
            @submit="submitTransfer"
            @close="closeTransfer"
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
