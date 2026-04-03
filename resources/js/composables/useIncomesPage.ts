import { computed, type Ref } from 'vue';
import {
    useTransactionListingPage,
    type TransactionListingFilters,
    type TransactionsPage,
} from '@/composables/useTransactionListingPage';
import { t } from '@/lib/i18n';

export type IncomeFilters = TransactionListingFilters;

export {
    ALL_CATEGORIES_VALUE,
    ALL_PAYMENT_METHODS_VALUE,
} from '@/composables/useTransactionListingPage';

export function useIncomesPage(options: {
    transactionsPage: Ref<TransactionsPage>;
    filters: Ref<IncomeFilters>;
    routePath?: string;
}) {
    const { transactionsPage, filters, routePath = '/incomes' } = options;
    const page = useTransactionListingPage({
        transactionsPage,
        filters,
        routePath,
        exportType: 'income',
        exportFilenamePrefix: 'prihodi',
        deleteSuccessMessage: t('finance.incomes.deleted'),
        deleteErrorMessage: t('finance.incomes.deleteError'),
        exportErrorMessage: 'Greška pri eksportovanju PDF-a.',
    });

    const averageIncome = computed(() =>
        page.transactions.value.length
            ? page.visibleAmountTotal.value / page.transactions.value.length
            : 0,
    );
    const accountLinkedCount = computed(
        () =>
            page.transactions.value.filter(
                (tx) => tx.payment_method === 'bank_account',
            ).length,
    );

    return {
        ...page,
        averageIncome,
        accountLinkedCount,
    };
}
