<script setup lang="ts">
import { computed } from 'vue';
import TransactionManagementToolbar from '@/components/transactions/TransactionManagementToolbar.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { RecurringTransactionTab } from '@/composables/useRecurringTransactionsPage';
import { t } from '@/lib/i18n';
import type { PaginationMeta } from '@/types/api';
import type { Category, RecurringTransaction } from '@/types/models';
import RecurringTransactionsFiltersPanel from './RecurringTransactionsFiltersPanel.vue';
import RecurringTransactionsTable from './RecurringTransactionsTable.vue';

const props = defineProps<{
    activeTab: RecurringTransactionTab;
    transactions: RecurringTransaction[];
    pagination: PaginationMeta;
    categories: Category[];
    accountsCount: number;
    search: string;
    showFilters: boolean;
    activeFiltersCount: number;
    categoryFilterValue: string;
    paymentMethodFilterValue: string;
    frequencyFilterValue: string;
    statusFilterValue: string;
    dateFrom: string;
    dateTo: string;
    perPage: string;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
    allPaymentMethodsValue: string;
    allFrequenciesValue: string;
    allStatusesValue: string;
}>();

const emit = defineEmits<{
    'update:activeTab': [value: RecurringTransactionTab];
    'update:search': [value: string];
    'update:showFilters': [value: boolean];
    'update:categoryFilterValue': [value: string];
    'update:paymentMethodFilterValue': [value: string];
    'update:frequencyFilterValue': [value: string];
    'update:statusFilterValue': [value: string];
    'update:dateFrom': [value: string];
    'update:dateTo': [value: string];
    applyFilters: [];
    clearFilters: [];
    pageChange: [page: number];
    perPageChange: [value: string];
    create: [];
    edit: [transaction: RecurringTransaction];
    activate: [transaction: RecurringTransaction];
    deactivate: [transaction: RecurringTransaction];
    delete: [transaction: RecurringTransaction];
}>();

const activeTabModel = computed({
    get: () => props.activeTab,
    set: (value: RecurringTransactionTab) => emit('update:activeTab', value),
});
</script>

<template>
    <section
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ t('finance.recurring.managementTitle') }}
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    {{ t('finance.recurring.managementDescription') }}
                </h2>
            </div>
            <div class="flex flex-1 flex-wrap justify-end gap-3">
                <TransactionManagementToolbar
                    :search="search"
                    :search-placeholder="
                        t('finance.recurring.searchPlaceholder')
                    "
                    :show-filters="showFilters"
                    :active-filters-count="activeFiltersCount"
                    :create-label="t('finance.recurring.add')"
                    :show-export="false"
                    @update:search="emit('update:search', $event)"
                    @toggle-filters="emit('update:showFilters', !showFilters)"
                    @create="emit('create')"
                />
            </div>
        </div>

        <Tabs v-model="activeTabModel" class="mt-5 space-y-5">
            <TabsList
                class="grid h-auto w-full grid-cols-2 rounded-2xl border border-border/60 bg-background p-1 md:w-fit"
            >
                <TabsTrigger value="expense">
                    {{ t('finance.recurring.expenseTitle') }}
                </TabsTrigger>
                <TabsTrigger value="income">
                    {{ t('finance.recurring.incomeTitle') }}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="expense" class="mt-0">
                <RecurringTransactionsFiltersPanel
                    v-if="showFilters"
                    class="mb-5"
                    :categories="
                        categories.filter(
                            (category) => category.type === 'expense',
                        )
                    "
                    :category-filter-value="categoryFilterValue"
                    :payment-method-filter-value="paymentMethodFilterValue"
                    :frequency-filter-value="frequencyFilterValue"
                    :status-filter-value="statusFilterValue"
                    :date-from="dateFrom"
                    :date-to="dateTo"
                    :all-payment-methods-value="allPaymentMethodsValue"
                    :all-frequencies-value="allFrequenciesValue"
                    :all-statuses-value="allStatusesValue"
                    @update:category-filter-value="
                        emit('update:categoryFilterValue', $event)
                    "
                    @update:payment-method-filter-value="
                        emit('update:paymentMethodFilterValue', $event)
                    "
                    @update:frequency-filter-value="
                        emit('update:frequencyFilterValue', $event)
                    "
                    @update:status-filter-value="
                        emit('update:statusFilterValue', $event)
                    "
                    @update:date-from="emit('update:dateFrom', $event)"
                    @update:date-to="emit('update:dateTo', $event)"
                    @apply-filters="emit('applyFilters')"
                    @clear-filters="emit('clearFilters')"
                />
                <RecurringTransactionsTable
                    type="expense"
                    :transactions="
                        transactions.filter((item) => item.type === 'expense')
                    "
                    :pagination="pagination"
                    :accounts-count="accountsCount"
                    :per-page="perPage"
                    :default-currency-code="defaultCurrencyCode"
                    :latest-exchange-rates="latestExchangeRates"
                    @create="emit('create')"
                    @edit="emit('edit', $event)"
                    @activate="emit('activate', $event)"
                    @deactivate="emit('deactivate', $event)"
                    @delete="emit('delete', $event)"
                    @page-change="emit('pageChange', $event)"
                    @per-page-change="emit('perPageChange', $event)"
                />
            </TabsContent>

            <TabsContent value="income" class="mt-0">
                <RecurringTransactionsFiltersPanel
                    v-if="showFilters"
                    class="mb-5"
                    :categories="
                        categories.filter(
                            (category) => category.type === 'income',
                        )
                    "
                    :category-filter-value="categoryFilterValue"
                    :payment-method-filter-value="paymentMethodFilterValue"
                    :frequency-filter-value="frequencyFilterValue"
                    :status-filter-value="statusFilterValue"
                    :date-from="dateFrom"
                    :date-to="dateTo"
                    :all-payment-methods-value="allPaymentMethodsValue"
                    :all-frequencies-value="allFrequenciesValue"
                    :all-statuses-value="allStatusesValue"
                    @update:category-filter-value="
                        emit('update:categoryFilterValue', $event)
                    "
                    @update:payment-method-filter-value="
                        emit('update:paymentMethodFilterValue', $event)
                    "
                    @update:frequency-filter-value="
                        emit('update:frequencyFilterValue', $event)
                    "
                    @update:status-filter-value="
                        emit('update:statusFilterValue', $event)
                    "
                    @update:date-from="emit('update:dateFrom', $event)"
                    @update:date-to="emit('update:dateTo', $event)"
                    @apply-filters="emit('applyFilters')"
                    @clear-filters="emit('clearFilters')"
                />
                <RecurringTransactionsTable
                    type="income"
                    :transactions="
                        transactions.filter((item) => item.type === 'income')
                    "
                    :pagination="pagination"
                    :accounts-count="accountsCount"
                    :per-page="perPage"
                    :default-currency-code="defaultCurrencyCode"
                    :latest-exchange-rates="latestExchangeRates"
                    @create="emit('create')"
                    @edit="emit('edit', $event)"
                    @activate="emit('activate', $event)"
                    @deactivate="emit('deactivate', $event)"
                    @delete="emit('delete', $event)"
                    @page-change="emit('pageChange', $event)"
                    @per-page-change="emit('perPageChange', $event)"
                />
            </TabsContent>
        </Tabs>
    </section>
</template>
