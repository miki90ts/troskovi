<script setup lang="ts">
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';
import TransactionManagementShell from '@/components/transactions/TransactionManagementShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { t } from '@/lib/i18n';
import type { PaginationMeta } from '@/types/api';
import type { Category, Transaction } from '@/types/models';
import WarrantiesFiltersPanel from './WarrantiesFiltersPanel.vue';
import WarrantiesTable from './WarrantiesTable.vue';

const props = defineProps<{
    transactions: Transaction[];
    categories: Category[];
    pagination: PaginationMeta;
    search: string;
    showFilters: boolean;
    activeFiltersCount: number;
    categoryFilterValue: string;
    paymentMethodFilterValue: string;
    statusFilterValue: string;
    dateFrom: string;
    dateTo: string;
    perPage: string;
    defaultCurrencyCode: string;
    latestExchangeRates: Record<string, number>;
    allPaymentMethodsValue: string;
    allStatusesValue: string;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'update:showFilters': [value: boolean];
    'update:categoryFilterValue': [value: string];
    'update:paymentMethodFilterValue': [value: string];
    'update:statusFilterValue': [value: string];
    'update:dateFrom': [value: string];
    'update:dateTo': [value: string];
    applyFilters: [];
    clearFilters: [];
    pageChange: [page: number];
    perPageChange: [value: string];
}>();

const searchModel = computed({
    get: () => props.search,
    set: (value: string) => emit('update:search', value),
});
</script>

<template>
    <TransactionManagementShell
        :eyebrow="t('finance.warranties.managementTitle')"
        :title="t('finance.warranties.managementDescription')"
    >
        <template #header-actions>
            <div class="relative min-w-0 flex-1 sm:w-72">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchModel"
                    :placeholder="t('finance.warranties.searchPlaceholder')"
                    class="h-11 rounded-2xl border-border/60 bg-background pl-9"
                />
            </div>

            <Button
                variant="outline"
                class="h-11 rounded-2xl border-border/60 px-4"
                @click="emit('update:showFilters', !showFilters)"
            >
                <SlidersHorizontal class="mr-2 h-4 w-4" />
                {{ t('common.actions.filters') }}
                <span
                    v-if="activeFiltersCount"
                    class="ml-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-primary/10 px-2 text-xs font-semibold text-primary"
                >
                    {{ activeFiltersCount }}
                </span>
            </Button>
        </template>

        <template #filters>
            <WarrantiesFiltersPanel
                v-if="showFilters"
                :categories="categories"
                :category-filter-value="categoryFilterValue"
                :payment-method-filter-value="paymentMethodFilterValue"
                :status-filter-value="statusFilterValue"
                :date-from="dateFrom"
                :date-to="dateTo"
                :all-payment-methods-value="allPaymentMethodsValue"
                :all-statuses-value="allStatusesValue"
                @update:category-filter-value="
                    emit('update:categoryFilterValue', $event)
                "
                @update:payment-method-filter-value="
                    emit('update:paymentMethodFilterValue', $event)
                "
                @update:status-filter-value="
                    emit('update:statusFilterValue', $event)
                "
                @update:date-from="emit('update:dateFrom', $event)"
                @update:date-to="emit('update:dateTo', $event)"
                @apply-filters="emit('applyFilters')"
                @clear-filters="emit('clearFilters')"
            />
        </template>

        <WarrantiesTable
            :transactions="transactions"
            :pagination="pagination"
            :per-page="perPage"
            :default-currency-code="defaultCurrencyCode"
            :latest-exchange-rates="latestExchangeRates"
            @page-change="emit('pageChange', $event)"
            @per-page-change="emit('perPageChange', $event)"
        />
    </TransactionManagementShell>
</template>
