import { computed, ref } from 'vue';
import { useDebts } from '@/composables/useDebts';
import { useToast } from '@/composables/useToast';
import { useValidationErrors } from '@/composables/useValidationErrors';
import { t } from '@/lib/i18n';
import { validateDebtForm } from '@/lib/validation/debtValidation';
import type { DebtFormValues } from '@/lib/validation/debtValidation';
import type { DebtPayload } from '@/types/api';
import type { CurrencySummary, Debt, DebtSummary } from '@/types/models';

export type DebtTab = 'i_owe' | 'owed_to_me';

export type DebtFormState = DebtFormValues;

function createEmptyForm(
    type: DebtTab = 'i_owe',
    defaultCurrencyId: number | null = null,
): DebtFormState {
    return {
        type,
        person_name: '',
        description: '',
        amount: '',
        currency_id: defaultCurrencyId ? String(defaultCurrencyId) : '',
        date: new Date().toISOString().split('T')[0],
        due_date: '',
        notes: '',
    };
}

export function useDebtsPage(
    initialDebts: Debt[],
    initialSummary: DebtSummary,
    _currencies: CurrencySummary[],
    defaultCurrencyId: number | null,
) {
    const { success, error: showError } = useToast();
    const { createDebt, updateDebt, deleteDebt } = useDebts();

    const debts = ref<Debt[]>([...initialDebts]);
    const summary = ref<DebtSummary>({ ...initialSummary });
    const activeTab = ref<DebtTab>('i_owe');
    const showForm = ref(false);
    const editingDebt = ref<Debt | null>(null);
    const formSubmitting = ref(false);
    const deleteTarget = ref<Debt | null>(null);
    const searchQuery = ref('');
    const statusFilter = ref<'all' | 'active' | 'settled' | 'overdue'>('all');
    const debtForm = ref<DebtFormState>(
        createEmptyForm('i_owe', defaultCurrencyId),
    );
    const {
        errors: formErrors,
        clearAllErrors,
        clearErrors,
        setErrors,
        setServerErrors,
    } = useValidationErrors<keyof DebtFormState>();

    const iOweDebts = computed(() =>
        debts.value.filter((d) => d.type === 'i_owe'),
    );
    const owedToMeDebts = computed(() =>
        debts.value.filter((d) => d.type === 'owed_to_me'),
    );

    const filteredDebts = computed(() => {
        let filtered = debts.value.filter((d) => d.type === activeTab.value);

        if (statusFilter.value !== 'all') {
            filtered = filtered.filter((d) => d.status === statusFilter.value);
        }

        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            filtered = filtered.filter(
                (d) =>
                    d.person_name.toLowerCase().includes(query) ||
                    d.description.toLowerCase().includes(query),
            );
        }

        return filtered;
    });

    const activeTabTotal = computed(() =>
        filteredDebts.value
            .filter((d) => d.status !== 'settled')
            .reduce((sum, d) => sum + d.remaining_amount, 0),
    );

    function openCreate() {
        editingDebt.value = null;
        debtForm.value = createEmptyForm(activeTab.value, defaultCurrencyId);
        clearAllErrors();
        showForm.value = true;
    }

    function openEdit(debt: Debt) {
        editingDebt.value = debt;
        debtForm.value = {
            type: debt.type,
            person_name: debt.person_name,
            description: debt.description,
            amount: String(debt.amount),
            currency_id: debt.currency
                ? String(debt.currency.id)
                : defaultCurrencyId
                  ? String(defaultCurrencyId)
                  : '',
            date: debt.date,
            due_date: debt.due_date ?? '',
            notes: debt.notes ?? '',
        };
        clearAllErrors();
        showForm.value = true;
    }

    function setDebtForm(value: DebtFormState) {
        const previous = debtForm.value;
        debtForm.value = value;

        const changedFields = (
            Object.keys(value) as (keyof DebtFormState)[]
        ).filter((field) => previous[field] !== value[field]);

        if (changedFields.length > 0) {
            clearErrors(...changedFields);
        }
    }

    function closeForm() {
        showForm.value = false;
        clearAllErrors();
    }

    function recalculateSummary() {
        const activeAndOverdue = debts.value.filter(
            (d) => d.status === 'active' || d.status === 'overdue',
        );
        summary.value = {
            total_i_owe: activeAndOverdue
                .filter((d) => d.type === 'i_owe')
                .reduce((sum, d) => sum + d.remaining_amount, 0),
            total_owed_to_me: activeAndOverdue
                .filter((d) => d.type === 'owed_to_me')
                .reduce((sum, d) => sum + d.remaining_amount, 0),
            active_count: debts.value.filter((d) => d.status === 'active')
                .length,
            overdue_count: debts.value.filter((d) => d.status === 'overdue')
                .length,
            settled_count: debts.value.filter((d) => d.status === 'settled')
                .length,
            total_count: debts.value.length,
            currency_code: initialSummary.currency_code,
            currency_symbol: initialSummary.currency_symbol,
        };
    }

    async function submitForm() {
        formSubmitting.value = true;

        try {
            clearAllErrors();

            const frontErrors = validateDebtForm(debtForm.value);

            if (Object.keys(frontErrors).length > 0) {
                setErrors(frontErrors);

                return;
            }

            if (
                debtForm.value.type !== 'i_owe' &&
                debtForm.value.type !== 'owed_to_me'
            ) {
                setErrors({ type: t('debts.typeRequired') });

                return;
            }

            const payload: DebtPayload = {
                type: debtForm.value.type,
                person_name: debtForm.value.person_name,
                description: debtForm.value.description,
                amount: parseFloat(debtForm.value.amount),
                currency_id: debtForm.value.currency_id
                    ? parseInt(debtForm.value.currency_id, 10)
                    : null,
                date: debtForm.value.date,
                due_date: debtForm.value.due_date || null,
                notes: debtForm.value.notes || null,
            };

            if (editingDebt.value) {
                const updated = await updateDebt(editingDebt.value.id, payload);
                const index = debts.value.findIndex(
                    (d) => d.id === editingDebt.value!.id,
                );

                if (index !== -1) {
                    debts.value.splice(index, 1, updated);
                }

                success(t('debts.updated'));
            } else {
                const created = await createDebt(payload);
                debts.value.push(created);
                success(t('debts.created'));
            }

            recalculateSummary();
            closeForm();
        } catch (e: any) {
            if (e.response?.status === 422) {
                setServerErrors(e.response?.data?.errors);
            } else {
                showError(t('debts.saveError'));
            }
        } finally {
            formSubmitting.value = false;
        }
    }

    async function handleDelete() {
        if (!deleteTarget.value) {
            return;
        }

        try {
            const id = deleteTarget.value.id;
            await deleteDebt(id);
            debts.value = debts.value.filter((d) => d.id !== id);
            recalculateSummary();
            success(t('debts.deleted'));
            deleteTarget.value = null;
        } catch {
            showError(t('debts.deleteError'));
        }
    }

    async function handleSettle(debt: Debt) {
        try {
            const updated = await updateDebt(debt.id, { status: 'settled' });
            const index = debts.value.findIndex((d) => d.id === debt.id);

            if (index !== -1) {
                debts.value.splice(index, 1, updated);
            }

            recalculateSummary();
            success(t('debts.settled'));
        } catch {
            showError(t('debts.settleError'));
        }
    }

    return {
        debts,
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
    };
}
