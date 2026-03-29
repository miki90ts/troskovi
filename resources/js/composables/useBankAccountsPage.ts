import { computed, ref } from 'vue';
import { useBankAccounts } from '@/composables/useBankAccounts';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type { BankAccount } from '@/types/models';

export type BankAccountFormState = {
    name: string;
    bank_name: string;
    account_number: string;
    currency: string;
    color: string;
    initial_balance: string;
};

export type TransferFormState = {
    from_account_id: string;
    to_account_id: string;
    amount: string;
    description: string;
};

function createEmptyForm(): BankAccountFormState {
    return {
        name: '',
        bank_name: '',
        account_number: '',
        currency: 'RSD',
        color: '#3b82f6',
        initial_balance: '0',
    };
}

function createEmptyTransferForm(): TransferFormState {
    return {
        from_account_id: '',
        to_account_id: '',
        amount: '',
        description: '',
    };
}

export function useBankAccountsPage(initialAccounts: BankAccount[]) {
    const { success, error: showError } = useToast();
    const {
        createAccount,
        updateAccount,
        archiveAccount,
        restoreAccount,
        transferFunds,
    } = useBankAccounts();

    const accounts = ref<BankAccount[]>([...initialAccounts]);
    const showForm = ref(false);
    const editingAccount = ref<BankAccount | null>(null);
    const formSubmitting = ref(false);
    const archiveConfirm = ref<BankAccount | null>(null);
    const showTransfer = ref(false);
    const transferSubmitting = ref(false);
    const accountForm = ref<BankAccountFormState>(createEmptyForm());
    const transferForm = ref<TransferFormState>(createEmptyTransferForm());

    const activeAccounts = computed(() =>
        accounts.value.filter((account) => !account.is_archived),
    );

    const archivedAccounts = computed(() =>
        accounts.value.filter((account) => account.is_archived),
    );

    const totalBalance = computed(() =>
        activeAccounts.value.reduce(
            (sum, account) => sum + account.current_balance,
            0,
        ),
    );

    const connectedBanks = computed(
        () =>
            new Set(activeAccounts.value.map((account) => account.bank_name))
                .size,
    );

    const colorPresets = [
        '#14b8a6',
        '#10b981',
        '#3b82f6',
        '#f97316',
        '#ef4444',
        '#8b5cf6',
    ];

    function openCreate() {
        editingAccount.value = null;
        accountForm.value = createEmptyForm();
        showForm.value = true;
    }

    function openEdit(account: BankAccount) {
        editingAccount.value = account;
        accountForm.value = {
            name: account.name,
            bank_name: account.bank_name,
            account_number: '',
            currency: account.currency,
            color: account.color ?? '#3b82f6',
            initial_balance: String(account.initial_balance),
        };
        showForm.value = true;
    }

    function applyPresetColor(color: string) {
        accountForm.value.color = color;
    }

    function openTransfer() {
        transferForm.value = createEmptyTransferForm();
        showTransfer.value = true;
    }

    async function submitForm() {
        formSubmitting.value = true;
        try {
            const payload = {
                ...accountForm.value,
                initial_balance: parseFloat(accountForm.value.initial_balance),
            };

            if (editingAccount.value) {
                const updated = await updateAccount(
                    editingAccount.value.id,
                    payload,
                );
                const index = accounts.value.findIndex(
                    (a) => a.id === editingAccount.value!.id,
                );
                if (index !== -1) {
                    accounts.value.splice(index, 1, updated);
                }
                success(t('finance.bankAccounts.updated'));
            } else {
                const created = await createAccount(payload);
                accounts.value.push(created);
                success(t('finance.bankAccounts.created'));
            }

            showForm.value = false;
        } catch {
            showError(t('finance.bankAccounts.saveError'));
        } finally {
            formSubmitting.value = false;
        }
    }

    async function handleArchive() {
        if (!archiveConfirm.value) return;
        try {
            const id = archiveConfirm.value.id;
            await archiveAccount(id);
            const index = accounts.value.findIndex((a) => a.id === id);
            if (index !== -1) {
                accounts.value.splice(index, 1, {
                    ...accounts.value[index],
                    is_archived: true,
                });
            }
            success(t('finance.bankAccounts.archivedSuccess'));
            archiveConfirm.value = null;
        } catch {
            showError(t('finance.bankAccounts.archiveError'));
        }
    }

    async function handleRestore(account: BankAccount) {
        try {
            const restored = await restoreAccount(account.id);
            const index = accounts.value.findIndex((a) => a.id === account.id);
            if (index !== -1) {
                accounts.value.splice(index, 1, restored);
            }
            success(t('finance.bankAccounts.restoredSuccess'));
        } catch {
            showError(t('finance.bankAccounts.restoreError'));
        }
    }

    async function submitTransfer() {
        transferSubmitting.value = true;
        try {
            await transferFunds({
                from_account_id: parseInt(transferForm.value.from_account_id),
                to_account_id: parseInt(transferForm.value.to_account_id),
                amount: parseFloat(transferForm.value.amount),
                description: transferForm.value.description || undefined,
                date: new Date().toISOString().split('T')[0],
            });

            // Re-fetch to get updated balances
            const { fetchAccounts } = useBankAccounts();
            const fresh = await fetchAccounts(true);
            accounts.value = fresh;

            success(t('finance.bankAccounts.transferSuccess'));
            showTransfer.value = false;
        } catch {
            showError(t('finance.bankAccounts.transferError'));
        } finally {
            transferSubmitting.value = false;
        }
    }

    return {
        accounts,
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
    };
}
