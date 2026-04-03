import { computed, type Ref } from 'vue';
import {
    useTransactionListingPage,
    type TransactionListingFilters,
    type TransactionsPage,
} from '@/composables/useTransactionListingPage';
import { t } from '@/lib/i18n';

export type ExpenseFilters = TransactionListingFilters;

export {
    ALL_CATEGORIES_VALUE,
    ALL_PAYMENT_METHODS_VALUE,
} from '@/composables/useTransactionListingPage';

export function useExpensesPage(options: {
    transactionsPage: Ref<TransactionsPage>;
    filters: Ref<ExpenseFilters>;
    routePath?: string;
}) {
    const { transactionsPage, filters, routePath = '/expenses' } = options;
    const page = useTransactionListingPage({
        transactionsPage,
        filters,
        routePath,
        exportType: 'expense',
        exportFilenamePrefix: 'troskovi',
        deleteSuccessMessage: t('finance.expenses.deleted'),
        deleteErrorMessage: t('finance.expenses.deleteError'),
        exportErrorMessage: 'Greška pri eksportovanju PDF-a.',
    });

    const averageExpense = computed(() =>
        page.transactions.value.length
            ? page.visibleAmountTotal.value / page.transactions.value.length
            : 0,
    );
    const bankPaidCount = computed(
        () =>
            page.transactions.value.filter(
                (tx) => tx.payment_method === 'bank_account',
            ).length,
    );

    return {
        ...page,
        averageExpense,
        bankPaidCount,
    };
}
