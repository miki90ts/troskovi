import { router } from '@inertiajs/vue3';
import { computed, ref  } from 'vue';
import type {Ref} from 'vue';
import { useRecurringTransactions } from '@/composables/useRecurringTransactions';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type { RecurringTransaction } from '@/types/models';

export type RecurringTransactionsPage = {
    data: RecurringTransaction[];
};

export type RecurringTransactionTab = 'expense' | 'income';
type PendingRecurringAction = 'deactivate' | 'delete';

export function useRecurringTransactionsPage(options: {
    recurringTransactionsPage: Ref<RecurringTransactionsPage>;
}) {
    const { recurringTransactionsPage } = options;
    const { success, error: showError } = useToast();
    const { deleteRecurring, updateRecurring } = useRecurringTransactions();

    const activeTab = ref<RecurringTransactionTab>('expense');
    const showForm = ref(false);
    const editingRecurring = ref<RecurringTransaction | null>(null);
    const actionTarget = ref<RecurringTransaction | null>(null);
    const pendingAction = ref<PendingRecurringAction | null>(null);

    const recurringTransactions = computed(
        () => recurringTransactionsPage.value.data,
    );
    const filteredRecurring = computed(() =>
        recurringTransactions.value.filter(
            (item) => item.type === activeTab.value,
        ),
    );
    const expenseRecurring = computed(() =>
        recurringTransactions.value.filter((item) => item.type === 'expense'),
    );
    const incomeRecurring = computed(() =>
        recurringTransactions.value.filter((item) => item.type === 'income'),
    );
    const activeTabDisplay = computed(() =>
        activeTab.value === 'expense'
            ? t('finance.recurring.expenseTitle')
            : t('finance.recurring.incomeTitle'),
    );
    const visibleAmountTotal = computed(() =>
        filteredRecurring.value.reduce((sum, item) => sum + item.amount, 0),
    );
    const confirmDialogTitle = computed(() =>
        pendingAction.value === 'delete'
            ? t('finance.recurring.deleteTitle')
            : t('finance.recurring.deactivateTitle'),
    );
    const confirmDialogDescription = computed(() =>
        pendingAction.value === 'delete'
            ? t('finance.recurring.deleteDescription')
            : t('finance.recurring.deactivateDescription'),
    );
    const confirmDialogConfirmText = computed(() =>
        pendingAction.value === 'delete'
            ? t('finance.recurring.deleteConfirm')
            : t('finance.recurring.deactivateConfirm'),
    );

    function openCreate() {
        editingRecurring.value = null;
        showForm.value = true;
    }

    function openEdit(item: RecurringTransaction) {
        editingRecurring.value = item;
        showForm.value = true;
    }

    function closeForm() {
        showForm.value = false;
        editingRecurring.value = null;
    }

    function onSaved() {
        closeForm();
        router.reload({ only: ['recurringTransactions'] });
    }

    function requestDeactivate(item: RecurringTransaction) {
        actionTarget.value = item;
        pendingAction.value = 'deactivate';
    }

    function requestDelete(item: RecurringTransaction) {
        actionTarget.value = item;
        pendingAction.value = 'delete';
    }

    function clearPendingAction() {
        actionTarget.value = null;
        pendingAction.value = null;
    }

    async function handleActivate(item: RecurringTransaction) {
        try {
            await updateRecurring(item.id, { is_active: true });
            success(t('finance.recurring.activated'));
            router.reload({ only: ['recurringTransactions'] });
        } catch {
            showError(t('finance.recurring.activateError'));
        }
    }

    async function handleConfirmedAction() {
        if (!actionTarget.value || !pendingAction.value) {
            return;
        }

        try {
            if (pendingAction.value === 'delete') {
                await deleteRecurring(actionTarget.value.id);
                success(t('finance.recurring.deleted'));
            } else {
                await updateRecurring(actionTarget.value.id, {
                    is_active: false,
                });
                success(t('finance.recurring.deactivated'));
            }

            clearPendingAction();
            router.reload({ only: ['recurringTransactions'] });
        } catch {
            showError(
                t(
                    pendingAction.value === 'delete'
                        ? 'finance.recurring.deleteError'
                        : 'finance.recurring.deactivateError',
                ),
            );
        }
    }

    return {
        activeTab,
        filteredRecurring,
        expenseRecurring,
        incomeRecurring,
        activeTabDisplay,
        visibleAmountTotal,
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
    };
}
