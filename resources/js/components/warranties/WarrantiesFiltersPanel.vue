<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import { computed } from 'vue';
import CategoryMultiSelect from '@/components/categories/CategoryMultiSelect.vue';
import PaymentMethodBadge from '@/components/transactions/PaymentMethodBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type { Category, Transaction } from '@/types/models';

const props = defineProps<{
    categories: Category[];
    categoryFilterValue: string;
    paymentMethodFilterValue: string;
    statusFilterValue: string;
    dateFrom: string;
    dateTo: string;
    allPaymentMethodsValue: string;
    allStatusesValue: string;
}>();

const emit = defineEmits<{
    'update:categoryFilterValue': [value: string];
    'update:paymentMethodFilterValue': [value: string];
    'update:statusFilterValue': [value: string];
    'update:dateFrom': [value: string];
    'update:dateTo': [value: string];
    applyFilters: [];
    clearFilters: [];
}>();

const categoryModel = computed({
    get: () => props.categoryFilterValue,
    set: (value: string) => emit('update:categoryFilterValue', value),
});
const paymentModel = computed({
    get: () => props.paymentMethodFilterValue,
    set: (value: string) => emit('update:paymentMethodFilterValue', value),
});
const statusModel = computed({
    get: () => props.statusFilterValue,
    set: (value: string) => emit('update:statusFilterValue', value),
});
const dateFromModel = computed({
    get: () => props.dateFrom,
    set: (value: string) => emit('update:dateFrom', value),
});
const dateToModel = computed({
    get: () => props.dateTo,
    set: (value: string) => emit('update:dateTo', value),
});
const selectedPaymentMethod = computed<Transaction['payment_method'] | null>(
    () =>
        paymentModel.value === 'cash' || paymentModel.value === 'bank_account'
            ? paymentModel.value
            : null,
);
</script>

<template>
    <div
        class="mt-5 grid gap-4 rounded-3xl border border-dashed border-border/70 bg-background/70 p-4 md:grid-cols-2 xl:grid-cols-6"
    >
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
            >
                {{ t('common.labels.category') }}
            </label>
            <CategoryMultiSelect
                v-model="categoryModel"
                :categories="categories"
                :placeholder="t('finance.expenses.allCategories')"
                :all-label="t('finance.warranties.filterAll')"
                trigger-class="h-11 rounded-2xl border-border/60 bg-background"
                @update:model-value="emit('applyFilters')"
            />
        </div>
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
            >
                {{ t('common.labels.paymentMethod') }}
            </label>
            <Select
                v-model="paymentModel"
                @update:model-value="emit('applyFilters')"
            >
                <SelectTrigger
                    class="h-11 rounded-2xl border-border/60 bg-background"
                >
                    <SelectValue>
                        <PaymentMethodBadge
                            v-if="selectedPaymentMethod"
                            :payment-method="selectedPaymentMethod"
                            compact
                        />
                    </SelectValue>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="allPaymentMethodsValue">
                        {{ t('finance.warranties.filterAll') }}
                    </SelectItem>
                    <SelectItem value="cash">
                        <PaymentMethodBadge payment-method="cash" compact />
                    </SelectItem>
                    <SelectItem value="bank_account">
                        <PaymentMethodBadge
                            payment-method="bank_account"
                            compact
                        />
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
            >
                Status
            </label>
            <Select
                v-model="statusModel"
                @update:model-value="emit('applyFilters')"
            >
                <SelectTrigger
                    class="h-11 rounded-2xl border-border/60 bg-background"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="allStatusesValue">{{
                        t('finance.warranties.filterAll')
                    }}</SelectItem>
                    <SelectItem value="active">{{
                        t('finance.warranties.filterActive')
                    }}</SelectItem>
                    <SelectItem value="expiring_soon">{{
                        t('finance.warranties.filterExpiringSoon')
                    }}</SelectItem>
                    <SelectItem value="expired">{{
                        t('finance.warranties.filterExpired')
                    }}</SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                >{{ t('common.labels.from') }}</label
            >
            <Input
                v-model="dateFromModel"
                type="date"
                class="h-11 rounded-2xl border-border/60 bg-background"
                @change="emit('applyFilters')"
            />
        </div>
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                >{{ t('common.labels.to') }}</label
            >
            <Input
                v-model="dateToModel"
                type="date"
                class="h-11 rounded-2xl border-border/60 bg-background"
                @change="emit('applyFilters')"
            />
        </div>
        <div class="flex items-end">
            <Button
                variant="ghost"
                class="h-11 w-full rounded-2xl"
                @click="emit('clearFilters')"
            >
                <Filter class="mr-2 h-4 w-4" />
                {{ t('common.actions.clearFilters') }}
            </Button>
        </div>
    </div>
</template>
