<script setup lang="ts">
import { computed } from 'vue';
import { Search } from 'lucide-vue-next';
import TransactionManagementShell from '@/components/transactions/TransactionManagementShell.vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type { PaginationMeta } from '@/types/api';
import type { Transaction } from '@/types/models';
import WarrantiesTable from './WarrantiesTable.vue';

const props = defineProps<{
    transactions: Transaction[];
    pagination: PaginationMeta;
    search: string;
    statusFilterValue: string;
    allStatusesValue: string;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'update:statusFilterValue': [value: string];
    pageChange: [page: number];
}>();

const searchModel = computed({
    get: () => props.search,
    set: (value: string) => emit('update:search', value),
});

const statusFilterModel = computed({
    get: () => props.statusFilterValue,
    set: (value: string) => emit('update:statusFilterValue', value),
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

            <Select v-model="statusFilterModel">
                <SelectTrigger
                    class="h-11 w-44 rounded-2xl border-border/60 bg-background"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="allStatusesValue">
                        {{ t('finance.warranties.filterAll') }}
                    </SelectItem>
                    <SelectItem value="active">
                        {{ t('finance.warranties.filterActive') }}
                    </SelectItem>
                    <SelectItem value="expiring_soon">
                        {{ t('finance.warranties.filterExpiringSoon') }}
                    </SelectItem>
                    <SelectItem value="expired">
                        {{ t('finance.warranties.filterExpired') }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </template>

        <WarrantiesTable
            :transactions="transactions"
            :pagination="pagination"
            @page-change="emit('pageChange', $event)"
        />
    </TransactionManagementShell>
</template>
