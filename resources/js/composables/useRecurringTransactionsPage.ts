import { router } from '@inertiajs/vue3';
import { computed, ref, type Ref } from 'vue';
import { useRecurringTransactions } from '@/composables/useRecurringTransactions';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type { RecurringTransaction } from '@/types/models';

export type RecurringTransactionsPage = {
    data: RecurringTransaction[];
};

export type RecurringTransactionTab = 'expense' | 'income';

export function useRecurringTransactionsPage(options: {
    recurringTransactionsPage: Ref<RecurringTransactionsPage>;
}) {
    const { recurringTransactionsPage } = options;
    const { success, error: showError } = useToast();
    const { deleteRecurring } = useRecurringTransactions();

    const activeTab = ref<RecurringTransactionTab>('expense');
    const showForm = ref(false);
    const editingRecurring = ref<RecurringTransaction | null>(null);
    const deactivateTarget = ref<RecurringTransaction | null>(null);

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
        router.reload();
    }

    async function handleDeactivate() {
        if (!deactivateTarget.value) {
            return;
        }

        try {
            await deleteRecurring(deactivateTarget.value.id);
            success(t('finance.recurring.deactivated'));
            deactivateTarget.value = null;
            router.reload();
        } catch {
            showError(t('finance.recurring.deactivateError'));
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
        deactivateTarget,
        openCreate,
        openEdit,
        closeForm,
        onSaved,
        handleDeactivate,
    };
}
