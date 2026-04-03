<script setup lang="ts">
import { computed } from 'vue';
import { Filter } from 'lucide-vue-next';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
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
import type { Category } from '@/types/models';

const props = defineProps<{
    categories: Category[];
    categoryFilterValue: string;
    paymentMethodFilterValue: string;
    dateFrom: string;
    dateTo: string;
    allCategoriesValue: string;
    allPaymentMethodsValue: string;
}>();

const emit = defineEmits<{
    'update:categoryFilterValue': [value: string];
    'update:paymentMethodFilterValue': [value: string];
    'update:dateFrom': [value: string];
    'update:dateTo': [value: string];
    applyFilters: [];
    clearFilters: [];
}>();

const categoryFilterModel = computed({
    get: () => props.categoryFilterValue,
    set: (value: string) => emit('update:categoryFilterValue', value),
});

const paymentMethodFilterModel = computed({
    get: () => props.paymentMethodFilterValue,
    set: (value: string) => emit('update:paymentMethodFilterValue', value),
});

const dateFromModel = computed({
    get: () => props.dateFrom,
    set: (value: string) => emit('update:dateFrom', value),
});

const dateToModel = computed({
    get: () => props.dateTo,
    set: (value: string) => emit('update:dateTo', value),
});

const selectedCategory = computed(
    () =>
        props.categories.find(
            (category) => String(category.id) === props.categoryFilterValue,
        ) ?? null,
);

const selectedPaymentMethod = computed(() => {
    return props.paymentMethodFilterValue || null;
});

const allLabel = computed(() =>
    t('common.actions.view').replace('Prikazi', 'Sve'),
);
</script>

<template>
    <div
        class="mt-5 grid gap-4 rounded-3xl border border-dashed border-border/70 bg-background/70 p-4 md:grid-cols-2 xl:grid-cols-5"
    >
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
            >
                {{ t('common.labels.category') }}
            </label>
            <Select
                v-model="categoryFilterModel"
                @update:model-value="emit('applyFilters')"
            >
                <SelectTrigger
                    class="h-11 rounded-2xl border-border/60 bg-background"
                >
                    <SelectValue
                        :placeholder="t('finance.incomes.allCategories')"
                    >
                        <CategoryBadge
                            v-if="selectedCategory"
                            :category="selectedCategory"
                            compact
                            class="max-w-full"
                        />
                    </SelectValue>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="allCategoriesValue">
                        {{ allLabel }}
                    </SelectItem>
                    <SelectItem
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="String(cat.id)"
                    >
                        <CategoryBadge
                            :category="cat"
                            compact
                            class="max-w-full"
                        />
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div class="grid gap-2">
            <label
                class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
            >
                {{ t('common.labels.paymentMethod') }}
            </label>
            <Select
                v-model="paymentMethodFilterModel"
                @update:model-value="emit('applyFilters')"
            >
                <SelectTrigger
                    class="h-11 rounded-2xl border-border/60 bg-background"
                >
                    <SelectValue :placeholder="t('finance.incomes.allMethods')">
                        <PaymentMethodBadge
                            v-if="selectedPaymentMethod"
                            :payment-method="selectedPaymentMethod"
                            compact
                            class="max-w-full"
                        />
                    </SelectValue>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="allPaymentMethodsValue">
                        {{ allLabel }}
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
                {{ t('common.labels.from') }}
            </label>
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
            >
                {{ t('common.labels.to') }}
            </label>
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
