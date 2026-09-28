import { computed, ref } from 'vue';
import { useBankAccounts } from '@/composables/useBankAccounts';
import { useToast } from '@/composables/useToast';
import { useValidationErrors } from '@/composables/useValidationErrors';
import { t } from '@/lib/i18n';
import { validateBankAccountForm } from '@/lib/validation/bankAccountValidation';
import type { BankAccountFormValues } from '@/lib/validation/bankAccountValidation';
import { validateTransferForm } from '@/lib/validation/transferValidation';
import type {
    AccountTransfer,
    BankAccount,
    CurrencySummary,
} from '@/types/models';

export type BankAccountFormState = BankAccountFormValues;

export type TransferFormState = {
    from_account_id: string;
    to_account_id: string;
    amount: string;
    description: string;
};

function createEmptyForm(
    defaultCurrencyId: number | null,
): BankAccountFormState {
    return {
        name: '',
        bank_name: '',
        account_number: '',
        currency_id: defaultCurrencyId ? String(defaultCurrencyId) : '',
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

export function useBankAccountsPage(
    initialAccounts: BankAccount[],
    initialTransfers: AccountTransfer[],
    currencies: CurrencySummary[],
    latestExchangeRates: Record<string, number>,
    defaultCurrencyId: number | null,
) {
    const { success, error: showError } = useToast();
    const {
        createAccount,
        updateAccount,
        archiveAccount,
        restoreAccount,
        transferFunds,
    } = useBankAccounts();

    const accounts = ref<BankAccount[]>([...initialAccounts]);
    const transfers = ref<AccountTransfer[]>([...initialTransfers]);
    const showForm = ref(false);
    const editingAccount = ref<BankAccount | null>(null);
    const formSubmitting = ref(false);
    const archiveConfirm = ref<BankAccount | null>(null);
    const showTransfer = ref(false);
    const transferSubmitting = ref(false);
    const accountForm = ref<BankAccountFormState>(
        createEmptyForm(defaultCurrencyId),
    );
    const transferForm = ref<TransferFormState>(createEmptyTransferForm());
    const {
        errors: formErrors,
        clearAllErrors,
        clearErrors,
        setErrors,
        setServerErrors,
    } = useValidationErrors<keyof BankAccountFormState>();
    const {
        errors: transferErrors,
        clearAllErrors: clearAllTransferErrors,
        clearErrors: clearTransferErrors,
        setErrors: setTransferErrors,
        setServerErrors: setTransferServerErrors,
    } = useValidationErrors<keyof TransferFormState>();

    const activeAccounts = computed(() =>
        accounts.value.filter((account) => !account.is_archived),
    );

    const defaultCurrency = computed(
        () =>
            currencies.find((currency) => currency.id === defaultCurrencyId) ??
            currencies.find((currency) => currency.iso_code === 'RSD') ??
            null,
    );

    const archivedAccounts = computed(() =>
        accounts.value.filter((account) => account.is_archived),
    );

    function resolveRate(currencyCode: string): number | null {
        if (currencyCode === 'RSD') {
            return 1;
        }

        const rate = latestExchangeRates[currencyCode];

        return typeof rate === 'number' && rate > 0 ? rate : null;
    }

    function convertCurrency(
        amount: number,
        fromCurrencyCode: string,
        toCurrencyCode: string,
    ): number {
        if (fromCurrencyCode === toCurrencyCode) {
            return amount;
        }

        const fromRate = resolveRate(fromCurrencyCode);
        const toRate = resolveRate(toCurrencyCode);

        if (!fromRate || !toRate) {
            return amount;
        }

        return Number(((amount * fromRate) / toRate).toFixed(2));
    }

    const totalBalance = computed(() =>
        activeAccounts.value.reduce((sum, account) => {
            const accountCurrency =
                account.currency_details?.iso_code ?? account.currency;

            return (
                sum +
                convertCurrency(
                    account.current_balance,
                    accountCurrency,
                    defaultCurrency.value?.iso_code ?? 'RSD',
                )
            );
        }, 0),
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
        accountForm.value = createEmptyForm(defaultCurrencyId);
        clearAllErrors();
        showForm.value = true;
    }

    function openEdit(account: BankAccount) {
        editingAccount.value = account;
        accountForm.value = {
            name: account.name,
            bank_name: account.bank_name,
            account_number: '',
            currency_id: account.currency_id ? String(account.currency_id) : '',
            color: account.color ?? '#3b82f6',
            initial_balance: String(account.initial_balance),
        };
        clearAllErrors();
        showForm.value = true;
    }

    function setAccountForm(value: BankAccountFormState) {
        const previous = accountForm.value;
        accountForm.value = value;

        const changedFields = (
            Object.keys(value) as (keyof BankAccountFormState)[]
        ).filter((field) => previous[field] !== value[field]);

        if (changedFields.length > 0) {
            clearErrors(...changedFields);
        }
    }

    function closeForm() {
        showForm.value = false;
        clearAllErrors();
    }

    function applyPresetColor(color: string) {
        accountForm.value.color = color;
    }

    function openTransfer() {
        transferForm.value = createEmptyTransferForm();
        clearAllTransferErrors();
        showTransfer.value = true;
    }

    const selectedFromAccount = computed(
        () =>
            activeAccounts.value.find(
                (account) =>
                    String(account.id) === transferForm.value.from_account_id,
            ) ?? null,
    );

    const selectedToAccount = computed(
        () =>
            activeAccounts.value.find(
                (account) =>
                    String(account.id) === transferForm.value.to_account_id,
            ) ?? null,
    );

    const transferPreview = computed(() => {
        const amount = Number(transferForm.value.amount);

        if (
            !selectedFromAccount.value ||
            !selectedToAccount.value ||
            Number.isNaN(amount) ||
            amount <= 0
        ) {
            return null;
        }

        const fromCurrency =
            selectedFromAccount.value.currency_details?.iso_code ??
            selectedFromAccount.value.currency;
        const toCurrency =
            selectedToAccount.value.currency_details?.iso_code ??
            selectedToAccount.value.currency;
        const fromRate = resolveRate(fromCurrency);
        const toRate = resolveRate(toCurrency);

        return {
            fromCurrency,
            toCurrency,
            sourceAmount: amount,
            destinationAmount:
                fromRate && toRate
                    ? convertCurrency(amount, fromCurrency, toCurrency)
                    : null,
        };
    });

    function setTransferForm(value: TransferFormState) {
        const previous = transferForm.value;
        transferForm.value = value;

        const changedFields = (
            Object.keys(value) as (keyof TransferFormState)[]
        ).filter((field) => previous[field] !== value[field]);

        if (changedFields.length > 0) {
            clearTransferErrors(...changedFields);
        }
    }

    function closeTransfer() {
        showTransfer.value = false;
        clearAllTransferErrors();
    }

    async function submitForm() {
        formSubmitting.value = true;

        try {
            clearAllErrors();

            const frontErrors = validateBankAccountForm(accountForm.value);

            if (Object.keys(frontErrors).length > 0) {
                setErrors(frontErrors);

                return;
            }

            const payload = {
                ...accountForm.value,
                currency_id: parseInt(accountForm.value.currency_id, 10),
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

            closeForm();
        } catch (e: any) {
            if (e.response?.status === 422) {
                setServerErrors(e.response?.data?.errors);
            } else {
                showError(t('finance.bankAccounts.saveError'));
            }
        } finally {
            formSubmitting.value = false;
        }
    }

    async function handleArchive() {
        if (!archiveConfirm.value) {
            return;
        }

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
            clearAllTransferErrors();

            const frontErrors = validateTransferForm(
                transferForm.value,
                activeAccounts.value,
            );

            if (Object.keys(frontErrors).length > 0) {
                setTransferErrors(frontErrors);

                return;
            }

            const createdTransfer = await transferFunds({
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
            transfers.value.unshift(createdTransfer);

            success(t('finance.bankAccounts.transferSuccess'));
            closeTransfer();
        } catch (e: any) {
            if (e.response?.status === 422) {
                setTransferServerErrors(e.response?.data?.errors);
            } else {
                showError(t('finance.bankAccounts.transferError'));
            }
        } finally {
            transferSubmitting.value = false;
        }
    }

    return {
        accounts,
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
    };
}
